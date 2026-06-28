<?php
// app/Http/Controllers/Admin/Pimpinan/DashboardController.php

namespace App\Http\Controllers\Admin\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->opd_id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak terikat dengan OPD tertentu.');
        }

        $periodeId = $request->periode_id;
        $periodes = $this->reportService->getPeriods();
        $data = $this->reportService->getAdminOPDDashboardData($user->opd_id, $periodeId);

        return view('admin.pimpinan.dashboard', compact(
            'data',
            'periodes',
            'periodeId'
        ));
    }
}