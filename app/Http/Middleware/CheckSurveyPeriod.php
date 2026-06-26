<?php
// app/Http/Middleware/CheckSurveyPeriod.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Survey\SurveyService;

class CheckSurveyPeriod
{
    protected SurveyService $surveyService;

    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function handle(Request $request, Closure $next)
    {
        $activePeriod = $this->surveyService->getActivePeriod();

        if (!$activePeriod) {
            return redirect()->route('survey.closed')
                ->with('error', 'Maaf, periode survei sedang tidak aktif. Silakan coba lagi nanti.');
        }

        session(['survey_period_id' => $activePeriod->id]);
        return $next($request);
    }
}