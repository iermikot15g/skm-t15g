<?php
// app/Services/Survey/IKMCalculator.php

namespace App\Services\Survey;

use App\Constants\IKMConstants;

class IKMCalculator
{
    /**
     * Calculate IKM from array of values (1-4)
     * 
     * @param array $values Array of 9 values (1-4)
     * @return array { average, total, ikm, category }
     * @throws \InvalidArgumentException
     */
    public function calculate(array $values): array
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
            'category' => $this->getCategory($ikm),
        ];
    }

    /**
     * Calculate IKM with detail per unsur
     */
    public function calculateWithDetail(array $values): array
    {
        $result = $this->calculate($values);
        
        // Tambahkan detail per unsur
        $detail = [];
        $unsurLabels = $this->getUnsurLabels();
        
        foreach ($values as $index => $value) {
            $detail[] = [
                'unsur' => $unsurLabels[$index] ?? "Unsur " . ($index + 1),
                'nilai' => $value,
                'label' => $this->getNilaiLabel($value),
            ];
        }

        $result['detail'] = $detail;
        return $result;
    }

    /**
     * Get category based on IKM value (PermenPANRB No. 14 Tahun 2017)
     */
    public function getCategory(float $ikm): array
    {
        return match(true) {
            $ikm >= IKMConstants::TARGET_IKM => [
                'code' => 'A',
                'label' => 'Sangat Baik',
                'color' => '#10B981',
                'bg_color' => 'bg-green-100',
                'text_color' => 'text-green-800',
            ],
            $ikm >= IKMConstants::BATAS_BAIK => [
                'code' => 'B',
                'label' => 'Baik',
                'color' => '#3B82F6',
                'bg_color' => 'bg-blue-100',
                'text_color' => 'text-blue-800',
            ],
            $ikm >= IKMConstants::BATAS_KURANG_BAIK => [
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

    private function getNilaiLabel(int $nilai): string
    {
        return match($nilai) {
            1 => 'Kurang',
            2 => 'Cukup',
            3 => 'Baik',
            4 => 'Sangat Baik',
            default => 'Tidak valid'
        };
    }

    private function getUnsurLabels(): array
    {
        return [
            'Persyaratan',
            'Prosedur',
            'Waktu Pelayanan',
            'Biaya/Tarif',
            'Produk Spesifikasi',
            'Kompetensi Pelaksana',
            'Perilaku Pelaksana',
            'Sarana dan Prasarana',
            'Penanganan Pengaduan'
        ];
    }
}