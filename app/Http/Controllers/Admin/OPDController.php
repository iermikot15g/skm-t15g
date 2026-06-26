<?php
// app/Http/Controllers/Admin/OPDController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OPD;
use Illuminate\Http\Request;

class OPDController extends Controller
{
    public function index()
    {
        $opds = OPD::withCount('layanans')->orderBy('nama_opd')->get();
        return view('admin.opd.index', compact('opds'));
    }

    public function create()
    {
        return view('admin.opd.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_opd' => 'required|string|max:50|unique:opd',
            'nama_opd' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        OPD::create($validated);

        return redirect()->route('admin.opd.index')
            ->with('success', 'OPD berhasil ditambahkan.');
    }

    public function edit(OPD $opd)
    {
        return view('admin.opd.edit', compact('opd'));
    }

    public function update(Request $request, OPD $opd)
    {
        $validated = $request->validate([
            'kode_opd' => 'required|string|max:50|unique:opd,kode_opd,' . $opd->id,
            'nama_opd' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $opd->update($validated);

        return redirect()->route('admin.opd.index')
            ->with('success', 'OPD berhasil diupdate.');
    }

    public function destroy(OPD $opd)
    {
        $opd->delete();
        return redirect()->route('admin.opd.index')
            ->with('success', 'OPD berhasil dihapus.');
    }

    public function toggle(OPD $opd)
    {
        $opd->update(['is_active' => !$opd->is_active]);
        return back()->with('success', 'Status OPD berhasil diubah.');
    }
}