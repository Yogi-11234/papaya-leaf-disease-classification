<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use App\Models\Pohon;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Display list of classification logs.
     */
    public function index(Request $request)
    {
        $query = Klasifikasi::with(['penyakit', 'pohon'])
            ->orderBy('waktu_klasifikasi', 'desc');

        // Apply filters
        if ($request->filled('grup')) {
            $query->whereHas('pohon', function ($q) use ($request) {
                $q->where('grup', $request->input('grup'));
            });
        }

        if ($request->filled('kode_pohon')) {
            $query->whereHas('pohon', function ($q) use ($request) {
                $q->where('kode_pohon', $request->input('kode_pohon'));
            });
        }

        $history = $query->paginate(12);

        // Fetch distinct groups and tree codes for filter dropdown options
        $groups = Pohon::select('grup')->distinct()->pluck('grup');
        $trees = Pohon::select('kode_pohon')->distinct()->pluck('kode_pohon');

        // Fetch all trees for quick-assignment dropdown
        $allTrees = Pohon::orderBy('grup')->orderBy('kode_pohon')->get();

        // Resolve selected tree model instance when filtering by tree code
        $selectedPohon = null;
        if ($request->filled('kode_pohon')) {
            $pQuery = Pohon::where('kode_pohon', $request->input('kode_pohon'));
            if ($request->filled('grup')) {
                $pQuery->where('grup', $request->input('grup'));
            }
            $selectedPohon = $pQuery->first();
        }

        return view('riwayat.index', compact('history', 'groups', 'trees', 'allTrees', 'selectedPohon'));
    }

    /**
     * Update tree assignment on a classification log via AJAX.
     */
    public function updateTreeAssignment(Request $request, $id_klasifikasi)
    {
        $request->validate([
            'id_pohon' => 'nullable|uuid|exists:pohon,id_pohon'
        ]);

        $klasifikasi = Klasifikasi::findOrFail($id_klasifikasi);
        $klasifikasi->id_pohon = $request->input('id_pohon');
        $klasifikasi->save();

        // Load relations for response
        $klasifikasi->load('pohon');

        return response()->json([
            'status' => 'success',
            'message' => 'Asosiasi pohon berhasil diperbarui.',
            'pohon' => $klasifikasi->pohon
        ]);
    }

    /**
     * Delete classification log.
     */
    public function destroy($id)
    {
        $klasifikasi = Klasifikasi::findOrFail($id);

        try {
            // Delete image file from server
            $filePath = public_path($klasifikasi->path_citra);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $klasifikasi->delete();

            return redirect()->route('riwayat.index')
                ->with('success', 'Riwayat klasifikasi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('riwayat.index')
                ->with('error', 'Gagal menghapus riwayat: ' . $e->getMessage());
        }
    }
}
