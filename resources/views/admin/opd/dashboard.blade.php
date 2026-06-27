{{-- resources/views/admin/opd/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard OPD')
@section('page-title', 'Dashboard ' . auth()->user()->opd->nama_opd)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Survei</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $stats['total_responses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">IKM</p>
                    <p class="text-2xl font-semibold text-gray-800">
                        {{ $stats['ikm'] ? $stats['ikm']['ikm'] . '%' : 'Belum ada data' }}
                    </p>
                    @if($stats['ikm'])
                        <span class="px-2 py-1 text-xs rounded-full {{ $stats['ikm']['category']['bg_color'] }} {{ $stats['ikm']['category']['text_color'] }}">
                            {{ $stats['ikm']['category']['label'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Layanan</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $stats['per_layanan']->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Per Layanan -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik per Layanan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Survei</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stats['per_layanan'] as $layanan)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $layanan->nama_layanan }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $layanan->total }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $layanan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $layanan->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada layanan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Data Responden (Tanpa NIK & HP) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Responden Terbaru</h3>
        <p class="text-sm text-gray-500 mb-3">*Data NIK dan Nomor HP dirahasiakan</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">JK</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pendidikan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pekerjaan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stats['recent_respondents'] as $respondent)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $respondent['nama'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $respondent['usia'] }} th</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $respondent['jenis_kelamin'] == 'L' ? 'L' : 'P' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $respondent['pendidikan'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $respondent['pekerjaan'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $respondent['layanan'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $respondent['submitted_at']->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada responden</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection