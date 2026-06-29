<?php
// app/Constants/IKMConstants.php

namespace App\Constants;

/**
 * Class IKMConstants
 * 
 * Centralized constants for IKM calculation and configuration
 * based on PermenPANRB No. 14 Tahun 2017
 */
class IKMConstants
{
    /**
     * Jumlah unsur dalam survei SKM (PermenPANRB No. 14 Tahun 2017)
     */
    public const UNSUUR_COUNT = 9;
    
    /**
     * Skala minimum jawaban
     */
    public const SKALA_MIN = 1;
    
    /**
     * Skala maksimum jawaban
     */
    public const SKALA_MAX = 4;
    
    /**
     * Nilai maksimum IKM (skala 1-4 dikonversi ke 25-100)
     */
    public const IKM_MAX = 100;
    
    /**
     * Nilai minimum IKM (skala 1-4 dikonversi ke 25-100)
     */
    public const IKM_MIN = 25;
    
    /**
     * Target IKM untuk kategori "Sangat Baik"
     */
    public const TARGET_IKM = 88.31;
    
    /**
     * Batas kategori "Baik"
     */
    public const BATAS_BAIK = 76.61;
    
    /**
     * Batas kategori "Kurang Baik"
     */
    public const BATAS_KURANG_BAIK = 65.00;
    
    /**
     * Jumlah maksimal responden yang ditampilkan di dashboard
     */
    public const MAX_RECENT_RESPONDENTS = 20;
    
    /**
     * Cache duration in seconds (1 hour)
     */
    public const CACHE_DURATION = 3600;
    
    /**
     * Get category based on IKM value
     */
    public static function getCategory(float $ikm): array
    {
        return match(true) {
            $ikm >= self::TARGET_IKM => [
                'code' => 'A',
                'label' => 'Sangat Baik',
                'color' => '#10B981',
                'bg_color' => 'bg-green-100',
                'text_color' => 'text-green-800',
            ],
            $ikm >= self::BATAS_BAIK => [
                'code' => 'B',
                'label' => 'Baik',
                'color' => '#3B82F6',
                'bg_color' => 'bg-blue-100',
                'text_color' => 'text-blue-800',
            ],
            $ikm >= self::BATAS_KURANG_BAIK => [
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
    
    /**
     * Get label for scale value (1-4)
     */
    public static function getScaleLabel(int $value, array $scaleMapping): string
    {
        return $scaleMapping[$value] ?? 'Tidak diketahui';
    }
}