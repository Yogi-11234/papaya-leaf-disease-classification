<?php

namespace App\Http\Controllers;

use App\Models\Pohon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PohonController extends Controller
{
    /**
     * Update details of a tree (kode_pohon and grup).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_pohon' => 'required|string|max:20',
            'grup' => 'required|string|max:50',
        ]);

        $pohon = Pohon::findOrFail($id);

        // Check uniqueness of the (kode_pohon, grup) combination except for the current record
        $exists = Pohon::where('kode_pohon', $request->input('kode_pohon'))
            ->where('grup', $request->input('grup'))
            ->where('id_pohon', '!=', $id)
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kombinasi Kode Pohon dan Blok/Grup sudah terdaftar.'
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kombinasi Kode Pohon dan Blok/Grup sudah terdaftar.');
        }

        $pohon->update([
            'kode_pohon' => $request->input('kode_pohon'),
            'grup' => $request->input('grup'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pohon berhasil diperbarui.',
                'pohon' => $pohon
            ]);
        }

        return redirect()->back()->with('success', 'Data pohon berhasil diperbarui.');
    }
}
