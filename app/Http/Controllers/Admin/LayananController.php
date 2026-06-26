<?php
// app/Http/Controllers/Admin/LayananController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\OPD;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::with('opd')->orderBy('nama_layanan')->get();
        return view('admin.layanan.index', compact('layanans'));
    }

    public function create()
    {
        $opds = OPD::where('is_active', true)->orderBy('nama_opd')->get();
        return view('admin.layanan.create', compact('opds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opd,id',
            'kode_layanan' => 'required|string|max:50|unique:layanan,kode_layanan,NULL,id,opd_id,' . $request->opd_id,
            'nama_layanan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Layanan::create($validated);

        return redirect()->route('admin.layanan.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Layanan $layanan)
    {
        $opds = OPD::where('is_active', true)->orderBy('nama_opd')->get();
        return view('admin.layanan.edit', compact('layanan', 'opds'));
    }

    public function update(Request $request, Layanan $layanan)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opd,id',
            'kode_layanan' => 'required|string|max:50|unique:layanan,kode_layanan,' . $layanan->id . ',id,opd_id,' . $request->opd_id,
            'nama_layanan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $layanan->update($validated);

        return redirect()->route('admin.layanan.index')
            ->with('success', 'Layanan berhasil diupdate.');
    }

    public function destroy(Layanan $layanan)
    {
        $layanan->delete();
        return redirect()->route('admin.layanan.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }

    public function toggle(Layanan $layanan)
    {
        $layanan->update(['is_active' => !$layanan->is_active]);
        return back()->with('success', 'Status layanan berhasil diubah.');
    }
}