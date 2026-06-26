<?php
// app/Http/Controllers/Public/SurveyController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Survey\SurveyService;
use App\Models\OPD;
use App\Models\PertanyaanSurvei;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    protected SurveyService $surveyService;

    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * Halaman Landing / Beranda
     */
    public function landing()
    {
        return view('public.landing');
    }

    /**
     * Pilih OPD
     */
    public function selectOPD()
    {
        try {
            $opds = $this->surveyService->getActiveOPDs();
            return view('public.survey.opd', compact('opds'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data OPD: ' . $e->getMessage());
        }
    }

    /**
     * Form Identitas
     */
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

    /**
     * Simpan Identitas
     */
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

    /**
     * Tampilkan 9 unsur pertanyaan
     */
    public function questions()
    {
        // Cek apakah data identitas ada di session
        if (!session()->has('survey_identity')) {
            return redirect()->route('survey.opd')
                ->with('error', 'Silakan isi identitas terlebih dahulu.');
        }

        // Ambil 9 pertanyaan dengan urutan yang benar
        $questions = PertanyaanSurvei::with('unsur')
            ->orderBy('urutan')
            ->get();

        return view('public.survey.questions', compact('questions'));
    }

    /**
     * Simpan jawaban dan redirect ke halaman kritik saran
     */
    public function storeQuestions(Request $request)
    {
        $request->validate([
            'answers' => 'required|array|size:9',
            'answers.*' => 'required|integer|between:1,4',
        ]);

        // Simpan jawaban di session
        session(['survey_answers' => $request->answers]);

        return redirect()->route('survey.kritik-saran')
            ->with('success', 'Jawaban berhasil disimpan.');
    }

    /**
     * Tampilkan halaman kritik & saran
     */
    public function kritikSaran()
    {
        // Cek apakah data identitas dan jawaban ada di session
        if (!session()->has('survey_identity') || !session()->has('survey_answers')) {
            return redirect()->route('survey.opd')
                ->with('error', 'Silakan isi identitas dan jawaban terlebih dahulu.');
        }

        return view('public.survey.kritik-saran');
    }

    /**
     * Simpan kritik & saran
     */
    public function storeKritikSaran(Request $request)
    {
        $request->validate([
            'kritik_saran' => 'nullable|string|max:1000',
        ]);

        session(['survey_kritik_saran' => $request->kritik_saran]);

        return redirect()->route('survey.review')
            ->with('success', 'Kritik & saran berhasil disimpan.');
    }

    /**
     * Tampilkan halaman review
     */
    public function review()
    {
        // Cek apakah semua data ada di session
        if (!session()->has('survey_identity') || 
            !session()->has('survey_answers') || 
            !session()->has('survey_kritik_saran')) {
            return redirect()->route('survey.opd')
                ->with('error', 'Data survei tidak lengkap. Silakan mulai dari awal.');
        }

        $identity = session('survey_identity');
        $answers = session('survey_answers');
        $kritikSaran = session('survey_kritik_saran');

        // Ambil data pertanyaan untuk ditampilkan di review
        $questions = PertanyaanSurvei::with('unsur')
            ->orderBy('urutan')
            ->get();

        return view('public.survey.review', compact('identity', 'answers', 'kritikSaran', 'questions'));
    }

    /**
     * Submit final survei
     */
    public function submit(Request $request)
    {
        // TODO: Implement submit ke database
        // Untuk sementara redirect ke thank you
        return view('public.survey.thank-you');
    }

    /**
     * Halaman survei ditutup
     */
    public function closed()
    {
        return view('public.survey.closed');
    }
}