<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeSurvei;
use App\Models\OPD;
use App\Services\Report\PDFGenerator;
use App\Services\Report\ExcelGenerator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected PDFGenerator $pdfGenerator;

    public function __construct(PDFGenerator $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Halaman laporan dengan filter
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $periodes = PeriodeSurvei::orderBy('created_at', 'desc')->get();
        
        // Tentukan OPD yang bisa diakses
        if ($user->isSuperAdmin() || $user->isPimpinanUtama()) {
            $opds = OPD::where('is_active', true)->orderBy('nama_opd')->get();
            $selectedOPD = $request->opd_id;
        } else {
            // Admin OPD atau Pimpinan OPD - hanya OPD sendiri
            $opds = $user->opd_id ? OPD::where('id', $user->opd_id)->get() : collect();
            $selectedOPD = $user->opd_id; // Force ke OPD user
        }

        $selectedPeriod = $request->periode_id;

        // Ambil data laporan untuk preview
        $reportData = $this->getReportData($selectedOPD, $selectedPeriod);

        return view('admin.laporan.index', compact(
            'periodes',
            'opds',
            'selectedPeriod',
            'selectedOPD',
            'reportData'
        ));
    }

    /**
     * Export PDF
     */
    public function exportPDF(Request $request)
    {
        $user = auth()->user();
        $periodeId = $request->periode_id;
        $opdId = $request->opd_id;

        // Validasi akses
        if (!$user->isSuperAdmin() && !$user->isPimpinanUtama()) {
            // Admin OPD atau Pimpinan OPD - hanya OPD sendiri
            if ($opdId && $opdId != $user->opd_id) {
                abort(403, 'Anda tidak memiliki akses ke OPD ini.');
            }
            $opdId = $user->opd_id;
        }

        if ($opdId && $opdId !== 'all' && $opdId !== null) {
            // PDF untuk satu OPD
            $pdf = $this->pdfGenerator->generateOPDReport($opdId, $periodeId);
            $opd = OPD::find($opdId);
            $filename = 'Laporan_SKM_' . ($opd ? $opd->kode_opd : 'OPD') . '_' . date('Ymd') . '.pdf';
            return $pdf->download($filename);
        } else {
            // PDF untuk semua OPD - hanya Super Admin & Pimpinan Utama
            if (!$user->isSuperAdmin() && !$user->isPimpinanUtama()) {
                abort(403, 'Anda tidak memiliki akses untuk melihat semua OPD.');
            }
            $pdf = $this->pdfGenerator->generateAllOPDReport($periodeId);
            $filename = 'Laporan_SKM_Semua_OPD_' . date('Ymd') . '.pdf';
            return $pdf->download($filename);
        }
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $periodeId = $request->periode_id;
        $opdId = $request->opd_id;

        // Validasi akses
        if (!$user->isSuperAdmin() && !$user->isPimpinanUtama()) {
            if ($opdId && $opdId != $user->opd_id) {
                abort(403, 'Anda tidak memiliki akses ke OPD ini.');
            }
            $opdId = $user->opd_id;
        }

        // Ambil data
        $data = $this->getExcelData($opdId, $periodeId);
        $headers = [
            'No',
            'Nama Responden',
            'Usia',
            'JK',
            'Pendidikan',
            'Pekerjaan',
            'Layanan',
            'Unsur 1',
            'Unsur 2',
            'Unsur 3',
            'Unsur 4',
            'Unsur 5',
            'Unsur 6',
            'Unsur 7',
            'Unsur 8',
            'Unsur 9',
            'IKM',
            'Kritik & Saran',
            'Tanggal Survei'
        ];

        $export = new ExcelGenerator($data, $headers);
        $filename = 'Data_SKM_' . date('Ymd') . '.xlsx';
        
        return Excel::download($export, $filename);
    }

    private function getReportData($opdId, $periodeId)
    {
        $query = \App\Models\SurveiResponse::where('status', 'completed')
            ->with(['responden', 'layanan', 'jawabans.pertanyaan.unsur']);

        if ($opdId && $opdId !== 'all' && $opdId !== null) {
            $query->whereHas('layanan', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $responses = $query->orderBy('submitted_at', 'desc')->limit(20)->get();
        $data = [];

        foreach ($responses as $response) {
            $nilai = $response->jawabans->pluck('nilai')->toArray();
            $ikm = count($nilai) === 9 ? round((array_sum($nilai) / 9) * 25, 2) : null;
            
            $calculator = app(\App\Services\Survey\IKMCalculator::class);
            $category = $ikm ? $calculator->getCategory($ikm) : null;

            $data[] = [
                'responden' => $response->responden->nama,
                'layanan' => $response->layanan->nama_layanan,
                'ikm' => $ikm ? $ikm . '%' : '-',
                'kategori' => $category,
                'tanggal' => $response->submitted_at->format('d/m/Y H:i'),
            ];
        }

        return $data;
    }

    private function getExcelData($opdId, $periodeId)
    {
        $query = \App\Models\SurveiResponse::where('status', 'completed')
            ->with(['responden', 'layanan', 'jawabans.pertanyaan.unsur']);

        if ($opdId && $opdId !== 'all' && $opdId !== null) {
            $query->whereHas('layanan', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $responses = $query->orderBy('submitted_at', 'desc')->get();
        $data = [];
        $no = 1;

        foreach ($responses as $response) {
            $jawaban = $response->jawabans->pluck('nilai', 'pertanyaan_id')->toArray();
            $unsurValues = [];
            foreach ($response->jawabans as $j) {
                $unsurId = $j->pertanyaan->unsur_id;
                $unsurValues[$unsurId] = $j->nilai;
            }

            $nilaiArray = array_values($jawaban);
            $ikm = count($nilaiArray) === 9 ? (array_sum($nilaiArray) / 9) * 25 : null;

            $row = [
                $no++,
                $response->responden->nama,
                $response->responden->usia,
                $response->responden->jenis_kelamin,
                $response->responden->pendidikan,
                $response->responden->pekerjaan,
                $response->layanan->nama_layanan,
                $unsurValues[1] ?? '',
                $unsurValues[2] ?? '',
                $unsurValues[3] ?? '',
                $unsurValues[4] ?? '',
                $unsurValues[5] ?? '',
                $unsurValues[6] ?? '',
                $unsurValues[7] ?? '',
                $unsurValues[8] ?? '',
                $unsurValues[9] ?? '',
                $ikm ? round($ikm, 2) : '',
                $response->kritik_saran ?? '',
                $response->submitted_at->format('d/m/Y H:i'),
            ];
            $data[] = $row;
        }

        return $data;
    }
}