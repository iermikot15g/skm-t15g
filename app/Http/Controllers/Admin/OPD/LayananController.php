<?php
// app/Http/Controllers/Admin/OPD/LayananController.php

namespace App\Http\Controllers\Admin\OPD;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\OPD;
use App\Models\SurveiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Pastikan user memiliki OPD
        if (!$user->opd_id) {
            return redirect()->route('admin.opd.dashboard')
                ->with('error', 'Anda tidak terikat dengan OPD tertentu.');
        }

        $layanans = Layanan::where('opd_id', $user->opd_id)
            ->withCount('surveiResponses')
            ->orderBy('nama_layanan')
            ->get();

        return view('admin.opd.layanan.index', compact('layanans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        if (!$user->opd_id) {
            return redirect()->route('admin.opd.dashboard')
                ->with('error', 'Anda tidak terikat dengan OPD tertentu.');
        }

        return view('admin.opd.layanan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'kode_layanan' => 'required|string|max:50|unique:layanan,kode_layanan,NULL,id,opd_id,' . $user->opd_id,
            'nama_layanan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['opd_id'] = $user->opd_id;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Layanan::create($validated);

        return redirect()->route('admin.opd.layanan.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Layanan $layanan)
    {
        $user = auth()->user();
        
        // Pastikan layanan milik OPD user
        if ($layanan->opd_id !== $user->opd_id) {
            abort(403, 'Anda tidak memiliki akses ke layanan ini.');
        }

        return view('admin.opd.layanan.edit', compact('layanan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Layanan $layanan)
    {
        $user = auth()->user();
        
        // Pastikan layanan milik OPD user
        if ($layanan->opd_id !== $user->opd_id) {
            abort(403, 'Anda tidak memiliki akses ke layanan ini.');
        }

        $validated = $request->validate([
            'kode_layanan' => 'required|string|max:50|unique:layanan,kode_layanan,' . $layanan->id . ',id,opd_id,' . $user->opd_id,
            'nama_layanan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $layanan->update($validated);

        return redirect()->route('admin.opd.layanan.index')
            ->with('success', 'Layanan berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Layanan $layanan)
    {
        $user = auth()->user();
        
        // Pastikan layanan milik OPD user
        if ($layanan->opd_id !== $user->opd_id) {
            abort(403, 'Anda tidak memiliki akses ke layanan ini.');
        }

        // Cek apakah layanan memiliki data survei
        $hasResponses = SurveiResponse::where('layanan_id', $layanan->id)->exists();
        
        if ($hasResponses) {
            return back()->with('error', 'Layanan ini memiliki data survei dan tidak dapat dihapus. Nonaktifkan saja.');
        }

        $layanan->delete();

        return redirect()->route('admin.opd.layanan.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }

    /**
     * Toggle status layanan.
     */
    public function toggle(Layanan $layanan)
    {
        $user = auth()->user();
        
        // Pastikan layanan milik OPD user
        if ($layanan->opd_id !== $user->opd_id) {
            abort(403, 'Anda tidak memiliki akses ke layanan ini.');
        }

        $layanan->update(['is_active' => !$layanan->is_active]);

        return back()->with('success', 'Status layanan berhasil diubah.');
    }
}