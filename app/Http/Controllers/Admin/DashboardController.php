<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OPD;
use App\Models\Layanan;
use App\Models\PeriodeSurvei;
use App\Models\SurveiResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik untuk Super Admin
        $stats = [
            'total_users' => User::count(),
            'total_opd' => OPD::count(),
            'total_layanan' => Layanan::count(),
            'total_survei' => SurveiResponse::where('status', 'completed')->count(),
            'total_periode' => PeriodeSurvei::count(),
            'active_periode' => PeriodeSurvei::where('is_active', true)
                ->where('tanggal_mulai', '<=', now())
                ->where('tanggal_selesai', '>=', now())
                ->first(),
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}