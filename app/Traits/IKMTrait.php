<?php
// app/Traits/IKMTrait.php

namespace App\Traits;

use App\Constants\IKMConstants;

/**
 * Trait IKMTrait
 * 
 * Provides common IKM calculation methods for services and controllers
 */
trait IKMTrait
{
    /**
     * Calculate IKM from array of values (1-4)
     * 
     * @param array $values Array of 9 values (1-4)
     * @return array { average, total, ikm, category }
     * @throws \InvalidArgumentException
     */
    protected function calculateIKM(array $values): array
    {
        if (count($values) !== IKMConstants::UNSUUR_COUNT) {
            throw new \InvalidArgumentException(
                'Harus ' . IKMConstants::UNSUUR_COUNT . ' nilai unsur'
            );
        }
        
        $total = array_sum($values);
        $average = $total / IKMConstants::UNSUUR_COUNT;
        
        // ✅ PERBAIKAN: IKM = Rata-rata × 25 (sesuai PermenPANRB)
        // Skala 1-4 dikonversi ke 25-100
        $ikm = $average * IKMConstants::KONVERSI_IKM;
        
        return [
            'average' => round($average, 2),
            'total' => $total,
            'ikm' => round($ikm, 2),
            'category' => IKMConstants::getCategory($ikm),
        ];
    }
    
    /**
     * Calculate IKM from collection of SurveiResponse
     * 
     * @param \Illuminate\Support\Collection $responses
     * @return array|null
     */
    protected function calculateIKMFromResponses($responses): ?array
    {
        if ($responses->isEmpty()) {
            return null;
        }
        
        $totalIKM = 0;
        $count = 0;
        
        foreach ($responses as $response) {
            $jawabans = $response->jawabans->pluck('nilai')->toArray();
            if (count($jawabans) === IKMConstants::UNSUUR_COUNT) {
                $result = $this->calculateIKM($jawabans);
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
     * Calculate single respondent IKM
     * 
     * @param \App\Models\SurveiResponse $response
     * @return float|null
     */
    protected function calculateSingleIKM($response): ?float
    {
        $nilai = $response->jawabans->pluck('nilai')->toArray();
        if (count($nilai) === IKMConstants::UNSUUR_COUNT) {
            // ✅ PERBAIKAN: IKM = Rata-rata × 25
            $average = array_sum($nilai) / IKMConstants::UNSUUR_COUNT;
            return round($average * IKMConstants::KONVERSI_IKM, 2);
        }
        return null;
    }
    
    /**
     * Calculate gap to target IKM
     * 
     * @param float|null $currentIKM
     * @return array|null
     */
    protected function calculateGap(?float $currentIKM): ?array
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