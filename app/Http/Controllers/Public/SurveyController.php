<?php
// app/Http/Controllers/Public/SurveyController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Survey\SurveyService;
use App\Models\OPD;
use App\Models\PertanyaanSurvei;
use App\Models\Responden;
use App\Models\SurveiResponse;
use App\Models\JawabanSurvei;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if (!session()->has('survey_identity')) {
            return redirect()->route('survey.opd')
                ->with('error', 'Silakan isi identitas terlebih dahulu.');
        }

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

        session(['survey_answers' => $request->answers]);

        return redirect()->route('survey.kritik-saran')
            ->with('success', 'Jawaban berhasil disimpan.');
    }

    /**
     * Tampilkan halaman kritik & saran
     */
    public function kritikSaran()
    {
        if (!session()->has('survey_identity') || !session()->has('survey_answers')) {
            return redirect()->route('survey.opd')
                ->with('error', 'Silakan isi identitas dan jawaban terlebih dahulu.');
        }

        return view('public.survey.kritik-saran');
    }

    /**
     * ✅ Simpan kritik & saran (OPSIONAL)
     */
    public function storeKritikSaran(Request $request)
    {
        $request->validate([
            'kritik_saran' => 'nullable|string|max:1000',
        ]);

        // ✅ Selalu set session, walaupun kosong
        session(['survey_kritik_saran' => $request->kritik_saran ?? null]);

        return redirect()->route('survey.review')
            ->with('success', 'Kritik & saran berhasil disimpan.');
    }

    /**
     * ✅ Tampilkan halaman review (Kritik & Saran OPSIONAL)
     */
    public function review()
    {
        // ✅ Hanya cek identity dan answers (wajib)
        if (!session()->has('survey_identity') || !session()->has('survey_answers')) {
            return redirect()->route('survey.opd')
                ->with('error', 'Data survei tidak lengkap. Silakan mulai dari awal.');
        }

        $identity = session('survey_identity');
        $answers = session('survey_answers');
        $kritikSaran = session('survey_kritik_saran'); // Bisa null (opsional)

        $questions = PertanyaanSurvei::with('unsur')
            ->orderBy('urutan')
            ->get();

        return view('public.survey.review', compact('identity', 'answers', 'kritikSaran', 'questions'));
    }

    /**
     * Submit final survei ke database
     */
    public function submit(Request $request)
    {
        try {
            DB::beginTransaction();

            $identity = session('survey_identity');
            $answers = session('survey_answers');
            $kritikSaran = session('survey_kritik_saran');
            $periodeId = session('survey_period_id');

            if (!$identity || !$answers) {
                return redirect()->route('survey.opd')
                    ->with('error', 'Data survei tidak lengkap. Silakan mulai dari awal.');
            }

            if (!$periodeId) {
                return redirect()->route('survey.opd')
                    ->with('error', 'Periode survei tidak ditemukan.');
            }

            $encryptedNik = Crypt::encryptString($identity['nik']);

            $existingResponden = Responden::where('nik', $encryptedNik)->first();

            if ($existingResponden) {
                $exists = SurveiResponse::where('responden_id', $existingResponden->id)
                    ->where('layanan_id', $identity['layanan_id'])
                    ->where('periode_id', $periodeId)
                    ->where('status', 'completed')
                    ->exists();

                if ($exists) {
                    return back()->with('error', 'Anda sudah mengisi survei untuk layanan ini di periode ini.');
                }
            }

            $responden = Responden::updateOrCreate(
                ['nik' => $encryptedNik],
                [
                    'nama' => $identity['nama'],
                    'hp' => Crypt::encryptString($identity['hp']),
                    'usia' => $identity['usia'],
                    'jenis_kelamin' => $identity['jenis_kelamin'],
                    'pendidikan' => $identity['pendidikan'],
                    'pekerjaan' => $identity['pekerjaan'],
                    'pekerjaan_lainnya' => $identity['pekerjaan_lainnya'] ?? null,
                ]
            );

            $surveiResponse = SurveiResponse::create([
                'responden_id' => $responden->id,
                'layanan_id' => $identity['layanan_id'],
                'periode_id' => $periodeId,
                'kritik_saran' => $kritikSaran ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reviewed_at' => now(),
                'submitted_at' => now(),
                'status' => 'completed',
            ]);

            foreach ($answers as $pertanyaanId => $nilai) {
                JawabanSurvei::create([
                    'survei_response_id' => $surveiResponse->id,
                    'pertanyaan_id' => $pertanyaanId,
                    'nilai' => $nilai,
                ]);
            }

            DB::commit();

            session()->forget([
                'survey_identity',
                'survey_answers',
                'survey_kritik_saran',
                'survey_opd_id',
                'survey_period_id'
            ]);

            $referenceCode = $this->generateReferenceCode($surveiResponse);

            return view('public.survey.thank-you', compact('referenceCode'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Survey Submit Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyimpan survei. Silakan coba lagi.');
        }
    }

    /**
     * Generate kode referensi
     */
    private function generateReferenceCode($surveiResponse): string
    {
        $opdId = $surveiResponse->layanan->opd_id ?? '00';
        return sprintf(
            'SKM-%s-%s-%s',
            date('Y'),
            str_pad($opdId, 3, '0', STR_PAD_LEFT),
            str_pad($surveiResponse->id, 6, '0', STR_PAD_LEFT)
        );
    }

    /**
     * Halaman survei ditutup
     */
    public function closed()
    {
        return view('public.survey.closed');
    }
}