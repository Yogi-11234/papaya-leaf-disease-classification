<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\Pohon;
use App\Models\Penyakit;
use App\Models\DetailProbabilitas;
use App\Services\FastApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ClassificationController extends Controller
{
    protected FastApiService $fastApiService;

    public function __construct(FastApiService $fastApiService)
    {
        $this->fastApiService = $fastApiService;
    }

    /**
     * Display the photo upload form.
     */
    public function index()
    {
        // Fetch existing tree codes and groups for autocomplete/quick selection
        $groups = Pohon::select('grup')->distinct()->pluck('grup');
        $trees = Pohon::all();

        return view('klasifikasi.index', compact('groups', 'trees'));
    }

    /**
     * Process classification request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:10240', // Max 10MB
            'kode_pohon' => 'nullable|string|max:20',
            'grup' => 'nullable|string|max:50',
            'new_kode_pohon' => 'nullable|string|max:20',
            'new_grup' => 'nullable|string|max:50',
        ]);

        try {
            // 1. Manage Tree & Group Association
            $idPohon = null;
            $kodePohon = $request->input('new_kode_pohon') ?: $request->input('kode_pohon');
            $grup = $request->input('new_grup') ?: $request->input('grup');

            if ($kodePohon && $grup) {
                // Check if tree already exists in this group
                $pohon = Pohon::where('kode_pohon', $kodePohon)
                    ->where('grup', $grup)
                    ->first();

                if (!$pohon) {
                    // Create new tree
                    $pohon = Pohon::create([
                        'kode_pohon' => $kodePohon,
                        'grup' => $grup
                    ]);
                }
                $idPohon = $pohon->id_pohon;
            }

            // 2. Handle uploaded file
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Save inside public folder for easy accessibility
            $uploadPath = public_path('uploads/classifications');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $fullImagePath = $uploadPath . '/' . $filename;
            $webPath = 'uploads/classifications/' . $filename;

            // 3. Contact FastAPI Inference service
            if (!$this->fastApiService->isHealthy()) {
                throw new Exception("Layanan Klasifikasi FastAPI sedang tidak aktif. Harap laporkan ke admin/pengembang.");
            }

            $prediction = $this->fastApiService->predict($fullImagePath);
            
            // Map prediction label to static penyakit code
            // CLASS_NAMES = ["Anthracnose", "BacterialSpot", "Curl", "RingSpot", "Healthy"]
            $labelPrediksi = $prediction['label_prediksi'];
            $confidence = $prediction['confidence'];
            $probabilities = $prediction['probabilities'];

            // Map string labels to penyakit primary keys
            $labelMapping = [
                'Anthracnose'   => 'P01',
                'BacterialSpot' => 'P02',
                'Curl'          => 'P03',
                'RingSpot'      => 'P04',
                'Healthy'       => 'P05'
            ];

            $kodePenyakit = $labelMapping[$labelPrediksi] ?? 'P05'; // Fallback to healthy if unknown

            // 4. Save classification details to DB
            $klasifikasi = Klasifikasi::create([
                'id_pohon' => $idPohon,
                'kode_penyakit' => $kodePenyakit,
                'path_citra' => $webPath,
                'label_prediksi' => $labelPrediksi,
                'confidence_score' => $confidence,
                'waktu_klasifikasi' => now()
            ]);

            // Save individual class probabilities for the chart
            foreach ($probabilities as $classLabel => $probValue) {
                $classKode = $labelMapping[$classLabel] ?? 'P05';
                DetailProbabilitas::create([
                    'id_klasifikasi' => $klasifikasi->id_klasifikasi,
                    'kode_penyakit' => $classKode,
                    'nilai_probabilitas' => $probValue
                ]);
            }

            return redirect()->route('klasifikasi.show', $klasifikasi->id_klasifikasi)
                ->with('success', 'Daun berhasil diklasifikasikan!');

        } catch (Exception $e) {
            Log::error("Classification error: " . $e->getMessage());
            
            // Clean up file if classification failed
            if (isset($fullImagePath) && file_exists($fullImagePath)) {
                @unlink($fullImagePath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display classification results.
     */
    public function show($id)
    {
        $klasifikasi = Klasifikasi::with(['penyakit', 'pohon', 'detailProbabilitas.penyakit'])
            ->findOrFail($id);

        // Sort probabilities according to standard order
        $standardOrder = ['P01', 'P02', 'P03', 'P04', 'P05'];
        $probabilities = $klasifikasi->detailProbabilitas->sortBy(function ($prob) use ($standardOrder) {
            return array_search($prob->kode_penyakit, $standardOrder);
        });

        return view('klasifikasi.show', compact('klasifikasi', 'probabilities'));
    }
}
