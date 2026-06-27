<?php
// app/Services/Report/PDFGenerator.php

namespace App\Services\Report;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PeriodeSurvei;
use App\Models\OPD;

class PDFGenerator
{
    /**
     * Generate PDF laporan untuk OPD tertentu
     */
    public function generateOPDReport($opdId, $periodeId = null)
    {
        $opd = OPD::findOrFail($opdId);
        $periode = $periodeId ? PeriodeSurvei::find($periodeId) : null;
        
        // Ambil data survei
        $responses = $this->getSurveyData($opdId, $periodeId);
        
        // Hitung IKM
        $ikmData = $this->calculateIKM($responses);
        
        $data = [
            'opd' => $opd,
            'periode' => $periode,
            'responses' => $responses,
            'ikmData' => $ikmData,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
        
        $pdf = Pdf::loadView('admin.laporan.pdf-opd', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generate PDF laporan semua OPD
     */
    public function generateAllOPDReport($periodeId = null)
    {
        $periode = $periodeId ? PeriodeSurvei::find($periodeId) : null;
        $opds = OPD::where('is_active', true)->get();
        
        $allData = [];
        foreach ($opds as $opd) {
            $responses = $this->getSurveyData($opd->id, $periodeId);
            $ikmData = $this->calculateIKM($responses);
            $allData[] = [
                'opd' => $opd,
                'responses' => $responses,
                'ikmData' => $ikmData,
            ];
        }
        
        $data = [
            'periode' => $periode,
            'allData' => $allData,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
        
        $pdf = Pdf::loadView('admin.laporan.pdf-all-opd', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf;
    }

    private function getSurveyData($opdId, $periodeId = null)
    {
        $query = \App\Models\SurveiResponse::whereHas('layanan', function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        })->where('status', 'completed')->with(['responden', 'layanan', 'jawabans.pertanyaan']);
        
        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }
        
        return $query->orderBy('submitted_at', 'desc')->get();
    }

    private function calculateIKM($responses)
    {
        if ($responses->isEmpty()) {
            return null;
        }

        $calculator = app(\App\Services\Survey\IKMCalculator::class);
        $totalIKM = 0;
        $count = 0;

        foreach ($responses as $response) {
            $jawabans = $response->jawabans->pluck('nilai')->toArray();
            if (count($jawabans) === 9) {
                $result = $calculator->calculate($jawabans);
                $totalIKM += $result['ikm'];
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        $averageIKM = round($totalIKM / $count, 2);
        $category = $calculator->getCategory($averageIKM);

        return [
            'ikm' => $averageIKM,
            'category' => $category,
            'total_responden' => $count,
            'per_unsur' => $this->calculatePerUnsur($responses),
        ];
    }

    private function calculatePerUnsur($responses)
    {
        $unsurValues = [];
        foreach ($responses as $response) {
            foreach ($response->jawabans as $jawaban) {
                $unsurId = $jawaban->pertanyaan->unsur_id;
                if (!isset($unsurValues[$unsurId])) {
                    $unsurValues[$unsurId] = [];
                }
                $unsurValues[$unsurId][] = $jawaban->nilai;
            }
        }

        $result = [];
        foreach ($unsurValues as $unsurId => $values) {
            $average = array_sum($values) / count($values);
            $result[$unsurId] = round($average, 2);
        }

        return $result;
    }
}