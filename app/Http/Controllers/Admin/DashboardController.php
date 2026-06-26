<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OPD;
use App\Models\Layanan;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalUsers' => User::count(),
            'totalOPD' => OPD::count(),
            'totalLayanan' => Layanan::count(),
        ];

        return view('admin.dashboard.index', $data);
    }
}