<?php
// app/Http/Controllers/Admin/PeriodeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeSurvei;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = PeriodeSurvei::with('creator')->orderBy('created_at', 'desc')->get();
        return view('admin.periode.index', compact('periodes'));
    }

    public function create()
    {
        return view('admin.periode.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        // Jika diaktifkan, nonaktifkan periode lain
        if ($request->is_active) {
            PeriodeSurvei::where('is_active', true)->update(['is_active' => false]);
        }

        PeriodeSurvei::create($validated);

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode survei berhasil ditambahkan.');
    }

    public function edit(PeriodeSurvei $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, PeriodeSurvei $periode)
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        // Jika diaktifkan, nonaktifkan periode lain
        if ($request->is_active) {
            PeriodeSurvei::where('is_active', true)->where('id', '!=', $periode->id)->update(['is_active' => false]);
        }

        $periode->update($validated);

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode survei berhasil diupdate.');
    }

    public function destroy(PeriodeSurvei $periode)
    {
        $periode->delete();
        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode survei berhasil dihapus.');
    }

    public function toggle(PeriodeSurvei $periode)
    {
        // Jika mengaktifkan, nonaktifkan yang lain
        if (!$periode->is_active) {
            PeriodeSurvei::where('is_active', true)->update(['is_active' => false]);
        }
        
        $periode->update(['is_active' => !$periode->is_active]);
        return back()->with('success', 'Status periode berhasil diubah.');
    }
}