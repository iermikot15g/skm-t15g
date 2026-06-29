<?php
// app/Services/Stats/IKMStatsService.php

namespace App\Services\Stats;

use App\Constants\IKMConstants;
use Illuminate\Support\Collection;

class IKMStatsService
{
    /**
     * Calculate IKM from array of values (1-4)
     */
    public function calculateIKM(array $values): array
    {
        if (count($values) !== IKMConstants::UNSUUR_COUNT) {
            throw new \InvalidArgumentException(
                'Harus ' . IKMConstants::UNSUUR_COUNT . ' nilai unsur'
            );
        }
        
        $total = array_sum($values);
        $average = $total / IKMConstants::UNSUUR_COUNT;
        $ikm = $average * (IKMConstants::IKM_MAX / IKMConstants::UNSUUR_COUNT);
        
        return [
            'average' => round($average, 2),
            'total' => $total,
            'ikm' => round($ikm, 2),
            'category' => IKMConstants::getCategory($ikm),
        ];
    }

    /**
     * Calculate IKM from collection of SurveiResponse
     */
    public function calculateFromResponses($responses): ?array
    {
        if ($responses->isEmpty()) {
            return null;
        }

        $totalIKM = 0;
        $count = 0;

        foreach ($responses as $response) {
            $jawabans = $response->jawabans->pluck('nilai')->toArray();
            if (count($jawabans) === IKMConstants::UNSUUR_COUNT) {
                $result = $this->calculateIKM($jawabans);  // ← PASTIKAN INI
                $totalIKM += $result['ikm'];
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        $averageIKM = round($totalIKM / $count, 2);

        return [
            'ikm' => $averageIKM,
            'category' => IKMConstants::getCategory($averageIKM),
            'total_responden' => $count,
        ];
    }

    /**
     * Calculate IKM per OPD
     */
    public function calculatePerOPD(Collection $responses): array
    {
        $opdData = [];
        $grouped = $responses->groupBy(function ($item) {
            return $item->layanan->opd_id ?? 'unknown';
        });

        foreach ($grouped as $opdId => $items) {
            $opd = \App\Models\OPD::find($opdId);
            if (!$opd) continue;

            $ikm = $this->calculateFromResponses($items);
            if ($ikm) {
                $opdData[] = [
                    'opd_id' => $opd->id,
                    'nama_opd' => $opd->nama_opd,
                    'total_survei' => $items->count(),
                    'ikm' => $ikm['ikm'],
                    'category' => $ikm['category'],
                ];
            }
        }

        usort($opdData, function ($a, $b) {
            return $b['ikm'] <=> $a['ikm'];
        });

        return $opdData;
    }

    /**
     * Calculate IKM per Layanan
     */
    public function calculatePerLayanan(Collection $responses): array
    {
        $result = [];
        $grouped = $responses->groupBy('layanan_id');

        foreach ($grouped as $layananId => $items) {
            $layanan = \App\Models\Layanan::find($layananId);
            if (!$layanan) continue;

            $ikm = $this->calculateFromResponses($items);
            if ($ikm) {
                $result[] = [
                    'layanan_id' => $layanan->id,
                    'nama_layanan' => $layanan->nama_layanan,
                    'total_survei' => $items->count(),
                    'ikm' => $ikm['ikm'],
                    'category' => $ikm['category'],
                ];
            }
        }

        usort($result, function ($a, $b) {
            return $b['ikm'] <=> $a['ikm'];
        });

        return $result;
    }

    /**
     * Calculate overall IKM
     */
    public function calculateOverall(Collection $responses): ?float
    {
        $result = $this->calculateFromResponses($responses);
        return $result ? $result['ikm'] : null;
    }

    /**
     * Get Top and Bottom OPD
     */
    public function getTopBottomOPD(array $ikmPerOPD): array
    {
        $top = array_slice($ikmPerOPD, 0, 5);
        $bottom = array_slice($ikmPerOPD, -5, 5);
        return ['top' => $top, 'bottom' => $bottom];
    }

    /**
     * Calculate gap to target
     */
    public function calculateGap(?float $currentIKM): ?array
    {
        if ($currentIKM === null) {
            return null;
        }

        $gap = round(IKMConstants::TARGET_IKM - $currentIKM, 2);

        return [
            'value' => $gap,
            'is_achieved' => $gap <= 0,
            'color' => $gap <= 0 ? '#10B981' : '#EF4444',
        ];
    }
}