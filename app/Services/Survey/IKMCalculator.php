<?php
// app/Services/Survey/IKMCalculator.php

namespace App\Services\Survey;

class IKMCalculator
{
    // ... method calculate dan calculateWithDetail tetap ...

    /**
     * Get category based on IKM value
     */
    public function getCategory(float $ikm): array
    {
        return match(true) {
            $ikm >= 88.31 => [
                'code' => 'A',
                'label' => 'Sangat Baik',
                'color' => '#10B981',
                'bg_color' => 'bg-green-100',
                'text_color' => 'text-green-800',
            ],
            $ikm >= 76.61 => [
                'code' => 'B',
                'label' => 'Baik',
                'color' => '#3B82F6',
                'bg_color' => 'bg-blue-100',
                'text_color' => 'text-blue-800',
            ],
            $ikm >= 65.00 => [
                'code' => 'C',
                'label' => 'Kurang Baik',
                'color' => '#F59E0B',
                'bg_color' => 'bg-yellow-100',
                'text_color' => 'text-yellow-800',
            ],
            default => [
                'code' => 'D',
                'label' => 'Tidak Baik',
                'color' => '#EF4444',
                'bg_color' => 'bg-red-100',
                'text_color' => 'text-red-800',
            ]
        };
    }

    // ... kode lainnya tetap ...
}