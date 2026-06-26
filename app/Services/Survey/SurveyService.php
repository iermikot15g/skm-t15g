<?php
// app/Services/Survey/SurveyService.php

namespace App\Services\Survey;

use App\Models\OPD;
use App\Models\Layanan;
use App\Models\PeriodeSurvei;

class SurveyService
{
    /**
     * Get active OPDs with their active services
     */
    public function getActiveOPDs()
    {
        return OPD::where('is_active', true)
            ->with(['layanans' => function ($query) {
                $query->where('is_active', true);
            }])
            ->has('layanans') // Hanya OPD yang punya layanan
            ->get();
    }

    /**
     * Get active services by OPD
     */
    public function getLayananByOPD($opdId)
    {
        return Layanan::where('opd_id', $opdId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get active survey period
     */
    public function getActivePeriod()
    {
        return PeriodeSurvei::active()->first();
    }

    /**
     * Check if survey period is active
     */
    public function hasActivePeriod(): bool
    {
        return $this->getActivePeriod() !== null;
    }
}