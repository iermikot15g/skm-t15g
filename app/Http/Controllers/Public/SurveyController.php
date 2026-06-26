<?php
// app/Http/Controllers/Public/SurveyController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Survey\SurveyService;
use App\Models\OPD;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    protected SurveyService $surveyService;

    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
        // MIDDLEWARE DIPINDAHKAN KE ROUTES (Laravel 12)
    }

    public function landing()
    {
        return view('public.landing');
    }

    public function selectOPD()
    {
        try {
            $opds = $this->surveyService->getActiveOPDs();
            return view('public.survey.opd', compact('opds'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data OPD: ' . $e->getMessage());
        }
    }

    public function identity(Request $request)
    {
        $request->validate([
            'opd_id' => 'required|exists:opd,id'
        ]);

        $opd = OPD::findOrFail($request->opd_id);
        $layanans = $this->surveyService->getLayananByOPD($request->opd_id);
        
        session(['survey_opd_id' => $request->opd_id]);

        return view('public.survey.identitas', compact('opd', 'layanans'));
    }

    public function storeIdentity(Request $request)
    {
        $validated = $request->validate([
            'layanan_id' => 'required|exists:layanan,id',
            'nik' => 'required|string|size:16|regex:/^[0-9]{16}$/',
            'nama' => 'required|string|max:100',
            'hp' => 'required|string|max:15|regex:/^[0-9]{10,15}$/',
            'usia' => 'required|integer|min:1|max:120',
            'jenis_kelamin' => 'required|in:L,P',
            'pendidikan' => 'required|in:SD/MI,SMP/MTs,SMA/MA/SMK,D1,D2,D3,D4/S1,S2,S3',
            'pekerjaan' => 'required|string|max:50',
            'pekerjaan_lainnya' => 'nullable|string|max:50|required_if:pekerjaan,Lainnya',
        ]);

        session(['survey_identity' => $validated]);

        return redirect()->route('survey.questions')
            ->with('success', 'Identitas berhasil disimpan.');
    }

    public function closed()
    {
        return view('public.survey.closed');
    }
}