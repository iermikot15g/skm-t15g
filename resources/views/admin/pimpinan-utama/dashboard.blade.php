{{-- resources/views/admin/pimpinan-utama/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Pimpinan Utama')
@section('page-title', 'Dashboard Pimpinan Utama')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('admin.utama.dashboard') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Periode Survei</label>
                <select name="periode_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Periode</option>
                    @foreach($periodes as $periode)
                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                            {{ $periode->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">OPD</label>
                <select name="opd_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all" {{ $selectedOPD == 'all' ? 'selected' : '' }}>Semua OPD</option>
                    @foreach($allStats as $stat)
                        <option value="{{ $stat['opd']->id }}" {{ $selectedOPD == $stat['opd']->id ? 'selected' : '' }}>
                            {{ $stat['opd']->nama_opd }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tampilkan
                </button>
                <a href="{{ route('admin.utama.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Reset
                </a>
            </div>
        </form>

        <!-- FORM EXPORT PDF -->
        <form action="{{ route('admin.utama.dashboard.export-pdf') }}" method="POST" class="flex items-end">
            @csrf
            <input type="hidden" name="periode_id" value="{{ $periodeId }}">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </button>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total OPD</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $totalOPD }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Survei</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $totalResponses }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">IKM Keseluruhan</p>
                    <p class="text-2xl font-semibold text-gray-800">
                        {{ $ikmOverall ? $ikmOverall . '%' : 'Belum ada data' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">OPD Terbaik</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $topOPD ? $topOPD['opd']->nama_opd : '-' }}
                        <span class="text-xs text-green-600">
                            {{ $topOPD ? $topOPD['ikm']['ikm'] . '%' : '' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik IKM per OPD</h3>
        @if(!empty($chartData['labels']))
            <div class="h-80">
                <canvas id="ikmChart"></canvas>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-lg font-medium">Belum ada data untuk ditampilkan</p>
                <p class="text-sm mt-1">Coba pilih periode atau OPD yang berbeda</p>
            </div>
        @endif
    </div>

    <!-- Tabel Semua OPD -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Rekapitulasi IKM per OPD</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Survei</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IKM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($allStats as $stat)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $stat['opd']->nama_opd }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $stat['total_responses'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $stat['ikm'] ? $stat['ikm']['ikm'] . '%' : 'Belum ada data' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($stat['ikm'])
                                    <span class="px-2 py-1 text-xs rounded-full {{ $stat['ikm']['category']['bg_color'] }} {{ $stat['ikm']['category']['text_color'] }}">
                                        {{ $stat['ikm']['category']['label'] }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada data OPD</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        
        if (chartData.labels && chartData.labels.length > 0) {
            const ctx = document.getElementById('ikmChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'IKM (%)',
                        data: chartData.data,
                        backgroundColor: chartData.colors.map(color => color + '80'),
                        borderColor: chartData.colors,
                        borderWidth: 2,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection