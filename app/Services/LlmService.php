<?php

namespace App\Services;

use App\Models\Klasifikasi;
use App\Models\KonsultasiChat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LlmService
{
    protected string $provider;
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->provider = env('LLM_PROVIDER', 'groq');
        $this->apiKey = env('LLM_API_KEY');
        $this->baseUrl = env('LLM_API_BASE_URL', '');
    }

    /**
     * Send chat query and return LLM response with grounded context.
     */
    public function getResponse(Klasifikasi $klasifikasi, string $userMessage): string
    {
        // 1. Fetch static disease information from DB
        $penyakit = $klasifikasi->penyakit;
        $namaPenyakit = $klasifikasi->label_prediksi;
        $confidence = round($klasifikasi->confidence_score * 100, 2);

        $deskripsi = $penyakit ? $penyakit->deskripsi : 'Tidak ada deskripsi rinci.';
        $rekomendasi = $penyakit ? $penyakit->rekomendasi_penanganan : 'Tidak ada rekomendasi penanganan khusus.';

        // 2. Fetch past chat history for this classification session
        $chatHistory = KonsultasiChat::where('id_klasifikasi', $klasifikasi->id_klasifikasi)
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Compile System Instruction
        $systemInstruction = "Anda adalah Asisten Pakar Penyakit Tanaman Pepaya (PapayaLeafAI).
Tugas Anda adalah membantu petani memahami hasil diagnosis klasifikasi penyakit daun pepaya dan cara penanganannya.
Hasil Deteksi Daun Saat Ini:
- Nama Penyakit Terdeteksi: {$namaPenyakit}
- Tingkat Keyakinan (Confidence): {$confidence}%
- Deskripsi Gejala: {$deskripsi}
- Rekomendasi Penanganan Awal: {$rekomendasi}

Aturan Penting:
1. Batasi topik percakapan HANYA seputar tanaman pepaya, penyakit daun pepaya, hama, pupuk, pengairan, dan cara bercocok tanam pepaya secara umum.
2. Jika pengguna bertanya tentang topik di luar pertanian pepaya (misal: pemrograman, politik, resep masakan, dll.), tolak dengan sopan dan ingatkan peran Anda.
3. Selalu sarankan petani untuk berkonsultasi dengan Dinas Pertanian atau penyuluh pertanian lapangan setempat untuk penanganan langsung jika kondisi lahan parah.
4. JANGAN pernah merevisi, mengubah, atau meragukan hasil klasifikasi penyakit yang dideteksi oleh model (yaitu {$namaPenyakit}) — peran Anda adalah menjelaskan dan menindaklanjutinya secara kontekstual.";

        // If no API Key, return offline static guideline response
        if (empty($this->apiKey)) {
            return $this->getOfflineFallbackResponse($namaPenyakit, $confidence, $rekomendasi, $userMessage);
        }

        try {
            if ($this->provider === 'groq') {
                return $this->callGroqApi($systemInstruction, $chatHistory, $userMessage);
            } else {
                return $this->callClaudeApi($systemInstruction, $chatHistory, $userMessage);
            }
        } catch (Exception $e) {
            Log::warning("LLM API Call failed: " . $e->getMessage() . ". Falling back to offline advice.");
            return $this->getOfflineFallbackResponse($namaPenyakit, $confidence, $rekomendasi, $userMessage) .
                   "\n\n*(Catatan: Pesan ini dihasilkan oleh sistem asisten offline karena kegagalan koneksi API LLM)*";
        }
    }


    /**
     * Call Groq API (OpenAI-compatible format)
     */
    protected function callGroqApi(string $systemInstruction, $chatHistory, string $userMessage): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemInstruction],
        ];

        foreach ($chatHistory as $chat) {
            $role = $chat->role === 'user' ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $chat->pesan];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

    $url = $this->baseUrl ?: "https://api.groq.com/openai/v1/chat/completions";

        $payload = [
            'model' => 'openai/gpt-oss-20b',
            'messages' => $messages,
            'max_tokens' => 1024,
            'temperature' => 0.4,
        ];

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            }
            throw new Exception("Format respon Groq tidak dikenal.");
        }

        throw new Exception("Groq API Error (HTTP " . $response->status() . "): " . $response->body());
    }

    /**
     * Call Anthropic Claude API
     */
    protected function callClaudeApi(string $systemInstruction, $chatHistory, string $userMessage): string
    {
        $messages = [];

        // Format history
        foreach ($chatHistory as $chat) {
            $role = $chat->role === 'user' ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $chat->pesan
            ];
        }

        // Add latest user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $url = $this->baseUrl ?: "https://api.anthropic.com/v1/messages";

        $payload = [
            'model' => 'claude-3-5-sonnet-20241022',
            'system' => $systemInstruction,
            'messages' => $messages,
            'max_tokens' => 1024,
            'temperature' => 0.4,
        ];

        $response = Http::timeout(15)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ])
            ->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['content'][0]['text'])) {
                return $data['content'][0]['text'];
            }
            throw new Exception("Format respon Claude tidak dikenal.");
        }

        throw new Exception("Claude API Error (HTTP " . $response->status() . "): " . $response->body());
    }

    /**
     * Offline Fallback Advice if API fails or is not set.
     */
    protected function getOfflineFallbackResponse(string $namaPenyakit, float $confidence, string $rekomendasi, string $query): string
    {
        $lowercaseQuery = strtolower($query);

        $introduction = "Halo! Saya adalah panduan konsultasi otomatis untuk penyakit **{$namaPenyakit}** (Hasil klasifikasi dengan confidence {$confidence}%).\n\n";

        if (str_contains($lowercaseQuery, 'obat') || str_contains($lowercaseQuery, 'pupuk') || str_contains($lowercaseQuery, 'tangani') || str_contains($lowercaseQuery, 'sembuh') || str_contains($lowercaseQuery, 'cara')) {
            return $introduction . "Berdasarkan rekomendasi penanganan resmi untuk **{$namaPenyakit}**:\n\n{$rekomendasi}\n\n*Disarankan melakukan pemangkasan teratur dan selalu membersihkan sisa daun layu dari sekitar area piringan batang pohon.*";
        }

        if (str_contains($lowercaseQuery, 'sebab') || str_contains($lowercaseQuery, 'kenapa') || str_contains($lowercaseQuery, 'patogen') || str_contains($lowercaseQuery, 'penularan')) {
            return $introduction . "Penyakit ini umumnya menyebar melalui spora jamur (untuk Antraknosa), cipratan air (untuk Bercak Bakteri), atau ditularkan oleh hama serangga penular (vektor) seperti Kutu Kebul pada penyakit Daun Keriting dan Kutu Daun pada penyakit Ringspot. Menjaga jarak tanam dan sirkulasi udara kebun adalah kunci utama meminimalisir penularan.";
        }

        return $introduction . "Maaf, saat ini layanan AI sedang dalam mode offline. Anda dapat menanyakan tentang:\n1. **Cara penanganan/obat** untuk penyakit {$namaPenyakit}.\n2. **Penyebab penularan** penyakit tersebut.\n\n*Berikut adalah panduan tindakan penanganan awal yang direkomendasikan:\n* " . str_replace("\n", "\n* ", $rekomendasi);
    }

    /**
     * Send chat query and return LLM response with tree history context.
     */
    public function getResponseForPohon(\App\Models\Pohon $pohon, string $userMessage): string
    {
        // 1. Fetch up to 10 latest classification history records for this tree
        $classifications = \App\Models\Klasifikasi::where('id_pohon', $pohon->id_pohon)
            ->orderBy('waktu_klasifikasi', 'desc')
            ->limit(10)
            ->get();

        // 2. Format history summary
        $historySummary = "Riwayat klasifikasi Pohon {$pohon->kode_pohon} (Blok {$pohon->grup}), telah diperiksa {$classifications->count()} kali:\n";
        foreach ($classifications as $c) {
            $dateStr = $c->waktu_klasifikasi->format('d M Y, H:i');
            $historySummary .= "- {$dateStr}: {$c->label_prediksi} (CI: " . round($c->confidence_score * 100, 2) . "%)\n";
        }

        // 3. Fetch details for all unique diseases that ever appeared on this tree
        $uniqueDiseaseCodes = $classifications->pluck('kode_penyakit')->unique()->toArray();
        $diseases = \App\Models\Penyakit::whereIn('kode_penyakit', $uniqueDiseaseCodes)->get();

        $diseaseDetails = "Detail penyakit yang pernah terdeteksi pada pohon ini:\n";
        foreach ($diseases as $d) {
            $diseaseDetails .= "=== {$d->nama_penyakit} ===\n";
            $diseaseDetails .= "Gejala: {$d->deskripsi}\n";
            $diseaseDetails .= "Penanganan: {$d->rekomendasi_penanganan}\n\n";
        }

        // 4. Compile System Instruction
        $systemInstruction = "Anda adalah Asisten Pakar Penyakit Tanaman Pepaya (PapayaLeafAI).
Tugas Anda adalah membantu petani memahami riwayat penyakit pohon pepaya ini dan cara penanganannya secara keseluruhan.
Konteks Riwayat Pohon Saat Ini:
{$historySummary}
{$diseaseDetails}

Aturan Penting:
1. Batasi topik percakapan HANYA seputar tanaman pepaya, penyakit daun pepaya, hama, pupuk, pengairan, dan cara bercocok tanam pepaya secara umum.
2. Jika pengguna bertanya tentang topik di luar pertanian pepaya (misal: pemrograman, politik, resep masakan, dll.), tolak dengan sopan dan ingatkan peran Anda.
3. Selalu sarankan petani untuk berkonsultasi dengan Dinas Pertanian atau penyuluh pertanian lapangan setempat untuk penanganan langsung jika kondisi lahan parah.
4. JANGAN pernah merevisi, mengubah, atau meragukan hasil klasifikasi penyakit yang sudah tercatat pada riwayat pohon ini — peran Anda adalah menjelaskan dan menindaklanjutinya secara kontekstual.";

        // 5. Fetch previous tree chat history
        $chatHistory = KonsultasiChat::where('id_pohon', $pohon->id_pohon)
            ->orderBy('created_at', 'asc')
            ->get();

        // 6. If no API Key, return offline static guideline response
        if (empty($this->apiKey)) {
            return $this->getOfflineFallbackResponseForPohon($pohon, $diseases, $userMessage);
        }

        try {
            if ($this->provider === 'groq') {
                return $this->callGroqApi($systemInstruction, $chatHistory, $userMessage);
            } else {
                return $this->callClaudeApi($systemInstruction, $chatHistory, $userMessage);
            }
        } catch (Exception $e) {
            Log::warning("LLM API Call failed: " . $e->getMessage() . ". Falling back to offline advice.");
            return $this->getOfflineFallbackResponseForPohon($pohon, $diseases, $userMessage) .
                   "\n\n*(Catatan: Pesan ini dihasilkan oleh sistem asisten offline karena kegagalan koneksi API LLM)*";
        }
    }

    /**
     * Offline fallback response for tree chatbot
     */
    protected function getOfflineFallbackResponseForPohon(\App\Models\Pohon $pohon, $diseases, string $query): string
    {
        $lowercaseQuery = strtolower($query);
        $diseaseNames = $diseases->pluck('nama_penyakit')->toArray();
        $diseaseNamesStr = implode(', ', $diseaseNames);

        $introduction = "Halo! Saya adalah panduan konsultasi otomatis untuk **Pohon {$pohon->kode_pohon}** (Blok {$pohon->grup}).\n";

        if (empty($diseaseNames)) {
            return $introduction . "Pohon ini belum memiliki catatan riwayat penyakit. Hubungkan hasil klasifikasi daun ke pohon ini untuk melihat riwayat kesehatannya.";
        }

        $introduction .= "Pohon ini tercatat pernah mengalami atau terdeteksi: **{$diseaseNamesStr}**.\n\n";

        if (str_contains($lowercaseQuery, 'obat') || str_contains($lowercaseQuery, 'pupuk') || str_contains($lowercaseQuery, 'tangani') || str_contains($lowercaseQuery, 'sembuh') || str_contains($lowercaseQuery, 'cara')) {
            $response = $introduction . "Berikut rekomendasi penanganan penyakit yang terdeteksi:\n\n";
            foreach ($diseases as $d) {
                $response .= "### Penanganan {$d->nama_penyakit}:\n{$d->rekomendasi_penanganan}\n\n";
            }
            return $response;
        }

        $response = $introduction . "Saat ini layanan AI sedang dalam mode offline. Berdasarkan riwayat penyakit pohon ini:\n\n";
        foreach ($diseases as $d) {
            $response .= "- **{$d->nama_penyakit}**: {$d->deskripsi}\n";
        }
        $response .= "\nSilakan tanyakan tentang **penanganan** atau **gejala** dari penyakit-penyakit di atas.";
        return $response;
    }
}
