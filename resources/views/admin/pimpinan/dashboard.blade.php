{{-- resources/views/admin/pimpinan/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Pimpinan OPD')
@section('page-title', 'Dashboard ' . auth()->user()->opd->nama_opd)

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="{{ route('admin.pimpinan.dashboard') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
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
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Tampilkan
                </button>
                <a href="{{ route('admin.pimpinan.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $data['stats']['total_survei'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Survei</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">
                {{ $data['stats']['ikm'] ? $data['stats']['ikm']['ikm'] . '%' : '-' }}
            </p>
            <p class="text-xs text-gray-500">IKM OPD</p>
            @if($data['stats']['ikm'])
                <span class="px-2 py-0.5 text-xs rounded-full {{ $data['stats']['ikm']['category']['bg_color'] }} {{ $data['stats']['ikm']['category']['text_color'] }}">
                    {{ $data['stats']['ikm']['category']['label'] }}
                </span>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            @php
                $trend = 'Stabil';
                $trendColor = 'text-gray-600';
                // Sederhana: bandingkan dengan target
                $ikm = $data['stats']['ikm'] ? $data['stats']['ikm']['ikm'] : null;
                $target = $data['stats']['target_ikm'] ?? 88.31;
                if ($ikm !== null) {
                    if ($ikm >= $target) {
                        $trend = '👍 Baik';
                        $trendColor = 'text-green-600';
                    } elseif ($ikm >= $target - 10) {
                        $trend = '📈 Perlu Perbaikan';
                        $trendColor = 'text-yellow-600';
                    } else {
                        $trend = '📉 Perlu Perhatian';
                        $trendColor = 'text-red-600';
                    }
                }
            @endphp
            <p class="text-2xl font-bold {{ $trendColor }}">{{ $trend }}</p>
            <p class="text-xs text-gray-500">Trend Kinerja</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            @php
                $gap = $data['stats']['ikm'] ? round($data['stats']['target_ikm'] - $data['stats']['ikm']['ikm'], 2) : null;
            @endphp
            <p class="text-2xl font-bold {{ $gap && $gap > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $gap !== null ? $gap . '%' : '-' }}
            </p>
            <p class="text-xs text-gray-500">Gap ke Target (88.31%)</p>
        </div>
    </div>

    <!-- Layanan Terbaik & Terendah -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">🏆 Layanan Terbaik</p>
            @if($data['layanan_terbaik'])
                <p class="text-lg font-semibold text-green-600">{{ $data['layanan_terbaik']['nama_layanan'] }}</p>
                <p class="text-sm text-gray-600">IKM: {{ $data['layanan_terbaik']['ikm'] }}%</p>
            @else
                <p class="text-sm text-gray-400">Belum ada data</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">⚠️ Layanan Terendah</p>
            @if($data['layanan_terendah'])
                <p class="text-lg font-semibold text-red-600">{{ $data['layanan_terendah']['nama_layanan'] }}</p>
                <p class="text-sm text-gray-600">IKM: {{ $data['layanan_terendah']['ikm'] }}%</p>
            @else
                <p class="text-sm text-gray-400">Belum ada data</p>
            @endif
        </div>
    </div>

    <!-- Grafik -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Tren IKM</h3>
            <div class="h-64">
                <canvas id="trenIKMChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🎯 Per Unsur</h3>
            <div class="h-64">
                <canvas id="unsurChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🍩 Distribusi Survei per Layanan</h3>
            <div class="h-64">
                <canvas id="distribusiLayananChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 IKM per Layanan</h3>
            <div class="h-64">
                <canvas id="ikmPerLayananChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Unsur Terkuat & Terlemah -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">💪 Unsur Terkuat</p>
            @if($data['unsur_terkuat'])
                <p class="text-lg font-semibold text-green-600">{{ $data['unsur_terkuat']['unsur'] }}</p>
                <p class="text-sm text-gray-600">Nilai: {{ $data['unsur_terkuat']['nilai'] }}</p>
            @else
                <p class="text-sm text-gray-400">Belum ada data</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">⚠️ Unsur Terlemah</p>
            @if($data['unsur_terlemah'])
                <p class="text-lg font-semibold text-red-600">{{ $data['unsur_terlemah']['unsur'] }}</p>
                <p class="text-sm text-gray-600">Nilai: {{ $data['unsur_terlemah']['nilai'] }}</p>
            @else
                <p class="text-sm text-gray-400">Belum ada data</p>
            @endif
        </div>
    </div>

    <!-- Tabel Rekap per Layanan -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Rekap IKM per Layanan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Total Survei</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">IKM</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data['ikm_per_layanan'] ?? [] as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['nama_layanan'] }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['total_survei'] }}</td>
                            <td class="px-4 py-2 text-sm text-center font-semibold">{{ $item['ikm'] }}%</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-1 text-xs rounded-full {{ $item['category']['bg_color'] }} {{ $item['category']['text_color'] }}">
                                    {{ $item['category']['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-2 text-center text-gray-500">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ikmPerLayanan = @json($data['ikm_per_layanan'] ?? []);
    const trenData = @json($data['tren_data'] ?? []);
    const unsurData = @json($data['unsur_data'] ?? []);
    const distribusiLayanan = @json($data['distribusi_layanan'] ?? []);

    // Tren IKM
    const ctx1 = document.getElementById('trenIKMChart');
    if (ctx1 && trenData.length > 0) {
        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: trenData[0]?.data.map(d => d.periode) || [],
                datasets: trenData.map((layanan, index) => ({
                    label: layanan.nama_layanan,
                    data: layanan.data.map(d => d.ikm),
                    borderColor: colors[index % colors.length],
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 3,
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } }
                },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                }
            }
        });
    }

    // Per Unsur
    const ctx2 = document.getElementById('unsurChart');
    if (ctx2 && unsurData.length > 0) {
        new Chart(ctx2, {
            type: 'radar',
            data: {
                labels: unsurData.map(item => item.unsur),
                datasets: [{
                    label: 'Nilai Rata-rata',
                    data: unsurData.map(item => item.nilai),
                    borderColor: '#3B82F6',
                    backgroundColor: '#3B82F620',
                    borderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { r: { min: 1, max: 4, ticks: { stepSize: 0.5 } } }
            }
        });
    }

    // Distribusi Layanan
    const ctx3 = document.getElementById('distribusiLayananChart');
    if (ctx3 && distribusiLayanan.length > 0) {
        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: distribusiLayanan.map(item => item.label),
                datasets: [{
                    data: distribusiLayanan.map(item => item.value),
                    backgroundColor: colors.slice(0, distribusiLayanan.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } }
                }
            }
        });
    }

    // IKM per Layanan
    const ctx4 = document.getElementById('ikmPerLayananChart');
    if (ctx4 && ikmPerLayanan.length > 0) {
        new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: ikmPerLayanan.map(item => item.nama_layanan),
                datasets: [{
                    label: 'IKM (%)',
                    data: ikmPerLayanan.map(item => item.ikm),
                    backgroundColor: ikmPerLayanan.map(item => item.category.color + '80'),
                    borderColor: ikmPerLayanan.map(item => item.category.color),
                    borderWidth: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection