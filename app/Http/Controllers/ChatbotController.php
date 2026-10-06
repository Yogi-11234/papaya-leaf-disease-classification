<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\KonsultasiChat;
use App\Services\LlmService;
use Illuminate\Http\Request;
use Exception;

class ChatbotController extends Controller
{
    protected LlmService $llmService;

    public function __construct(LlmService $llmService)
    {
        $this->llmService = $llmService;
    }

    /**
     * Get chat logs for a specific classification session.
     */
    public function getChatHistory($id)
    {
        $chatLogs = KonsultasiChat::where('id_klasifikasi', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($chatLogs);
    }

    /**
     * Send message to Chatbot and receive assistant response.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $klasifikasi = Klasifikasi::findOrFail($id);
        $userMessage = $request->input('message');

        try {
            // 1. Save user message to database
            $userChat = KonsultasiChat::create([
                'id_klasifikasi' => $klasifikasi->id_klasifikasi,
                'role' => 'user',
                'pesan' => $userMessage,
                'created_at' => now(),
            ]);

            // 2. Fetch AI reply from service
            $aiReplyText = $this->llmService->getResponse($klasifikasi, $userMessage);

            // 3. Save AI message to database
            $assistantChat = KonsultasiChat::create([
                'id_klasifikasi' => $klasifikasi->id_klasifikasi,
                'role' => 'assistant',
                'pesan' => $aiReplyText,
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'user_chat' => $userChat,
                'ai_chat' => $assistantChat,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan tanggapan dari Chatbot AI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat logs for a specific tree session.
     */
    public function getTreeChatHistory($id_pohon)
    {
        $chatLogs = KonsultasiChat::where('id_pohon', $id_pohon)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($chatLogs);
    }

    /**
     * Send message to Tree Chatbot and receive assistant response.
     */
    public function sendTreeMessage(Request $request, $id_pohon)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $pohon = \App\Models\Pohon::findOrFail($id_pohon);
        $userMessage = $request->input('message');

        try {
            // 1. Save user message to database
            $userChat = KonsultasiChat::create([
                'id_pohon' => $pohon->id_pohon,
                'role' => 'user',
                'pesan' => $userMessage,
                'created_at' => now(),
            ]);

            // 2. Fetch AI reply from service
            $aiReplyText = $this->llmService->getResponseForPohon($pohon, $userMessage);

            // 3. Save AI message to database
            $assistantChat = KonsultasiChat::create([
                'id_pohon' => $pohon->id_pohon,
                'role' => 'assistant',
                'pesan' => $aiReplyText,
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'user_chat' => $userChat,
                'ai_chat' => $assistantChat,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan tanggapan dari Chatbot AI: ' . $e->getMessage()
            ], 500);
        }
    }
}
