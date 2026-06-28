{{-- resources/views/admin/opd/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard OPD')
@section('page-title', 'Dashboard ' . auth()->user()->opd->nama_opd)

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- ========================================== -->
    <!-- FILTER -->
    <!-- ========================================== -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="{{ route('admin.opd.dashboard') }}" method="GET" class="flex flex-wrap items-end gap-4">
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
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tampilkan
                </button>
                <a href="{{ route('admin.opd.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Reset
                </a>
            </div>
        </form>

        <!-- FORM EXPORT PDF - TERPISAH -->
        <form action="{{ route('admin.opd.dashboard.export-pdf') }}" method="POST" class="flex items-end">
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

    <!-- ========================================== -->
    <!-- STATISTIK CARDS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $data['stats']['total_survei'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Survei</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $data['stats']['total_layanan'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Layanan</p>
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
                $gap = $data['stats']['ikm'] ? round($data['stats']['target_ikm'] - $data['stats']['ikm']['ikm'], 2) : null;
            @endphp
            <p class="text-2xl font-bold {{ $gap && $gap > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $gap !== null ? $gap . '%' : '-' }}
            </p>
            <p class="text-xs text-gray-500">Gap ke Target (88.31%)</p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- LAYANAN TERBAIK & TERENDAH -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Layanan Terbaik</p>
            @if($data['layanan_terbaik'])
                <p class="text-lg font-semibold text-green-600">{{ $data['layanan_terbaik']['nama_layanan'] }}</p>
                <p class="text-sm text-gray-600">IKM: {{ $data['layanan_terbaik']['ikm'] }}%</p>
            @else
                <p class="text-sm text-gray-400">Belum ada data</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Layanan Terendah</p>
            @if($data['layanan_terendah'])
                <p class="text-lg font-semibold text-red-600">{{ $data['layanan_terendah']['nama_layanan'] }}</p>
                <p class="text-sm text-gray-600">IKM: {{ $data['layanan_terendah']['ikm'] }}%</p>
            @else
                <p class="text-sm text-gray-400">Belum ada data</p>
            @endif
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRAFIK ROW 1: IKM per Layanan + Tren -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 IKM per Layanan</h3>
            <div class="h-64">
                <canvas id="ikmPerLayananChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Tren IKM</h3>
            <div class="h-64">
                <canvas id="trenIKMChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRAFIK ROW 2: Per Unsur + Distribusi -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🎯 Per Unsur</h3>
            <div class="h-64">
                <canvas id="unsurChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🍩 Distribusi Survei per Layanan</h3>
            <div class="h-64">
                <canvas id="distribusiLayananChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- DEMOGRAFI RESPONDEN -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">👤 Jenis Kelamin</h3>
            <div class="h-48">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🎓 Pendidikan</h3>
            <div class="h-48">
                <canvas id="pendidikanChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">💼 Pekerjaan</h3>
            <div class="h-48">
                <canvas id="pekerjaanChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- UNSUR TERKUAT & TERLEMAH -->
    <!-- ========================================== -->
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

    <!-- ========================================== -->
    <!-- TABEL REKAP PER LAYANAN -->
    <!-- ========================================== -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
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

    <!-- ========================================== -->
    <!-- TABEL RESPONDEN TERBARU -->
    <!-- ========================================== -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Responden Terbaru</h3>
        <p class="text-sm text-gray-500 mb-3">*Data NIK dan Nomor HP dirahasiakan</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Usia</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">JK</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pendidikan</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pekerjaan</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">IKM</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data['recent_respondents'] ?? [] as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['nama'] }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['usia'] }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['jenis_kelamin'] == 'L' ? 'L' : 'P' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $item['pendidikan'] }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $item['pekerjaan'] }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $item['layanan'] }}</td>
                            <td class="px-4 py-2 text-sm text-center font-semibold">{{ $item['ikm'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['tanggal'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-2 text-center text-gray-500">Belum ada data survei</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- CHART.JS SCRIPTS -->
<!-- ========================================== -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari server
    const ikmPerLayanan = @json($data['ikm_per_layanan'] ?? []);
    const trenData = @json($data['tren_data'] ?? []);
    const unsurData = @json($data['unsur_data'] ?? []);
    const distribusiLayanan = @json($data['distribusi_layanan'] ?? []);
    const demografi = @json($data['demografi'] ?? []);

    // ==========================================
    // 1. IKM per Layanan (Bar Chart)
    // ==========================================
    const ctx1 = document.getElementById('ikmPerLayananChart');
    if (ctx1 && ikmPerLayanan.length > 0) {
        new Chart(ctx1, {
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

    // ==========================================
    // 2. Tren IKM (Line Chart)
    // ==========================================
    const ctx2 = document.getElementById('trenIKMChart');
    if (ctx2 && trenData.length > 0) {
        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: trenData[0]?.data.map(d => d.periode) || [],
                datasets: trenData.map((layanan, index) => ({
                    label: layanan.nama_layanan,
                    data: layanan.data.map(d => d.ikm),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '20',
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

    // ==========================================
    // 3. Per Unsur (Radar Chart)
    // ==========================================
    const ctx3 = document.getElementById('unsurChart');
    if (ctx3 && unsurData.length > 0) {
        new Chart(ctx3, {
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

    // ==========================================
    // 4. Distribusi Layanan (Pie Chart)
    // ==========================================
    const ctx4 = document.getElementById('distribusiLayananChart');
    if (ctx4 && distribusiLayanan.length > 0) {
        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'];
        new Chart(ctx4, {
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

    // ==========================================
    // 5. Gender (Pie Chart)
    // ==========================================
    const ctx5 = document.getElementById('genderChart');
    if (ctx5 && demografi.gender) {
        new Chart(ctx5, {
            type: 'pie',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [demografi.gender.L || 0, demografi.gender.P || 0],
                    backgroundColor: ['#3B82F6', '#EC4899'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 9 } } }
                }
            }
        });
    }

    // ==========================================
    // 6. Pendidikan (Bar Chart)
    // ==========================================
    const ctx6 = document.getElementById('pendidikanChart');
    if (ctx6 && demografi.pendidikan) {
        const keys = Object.keys(demografi.pendidikan);
        const values = keys.map(k => demografi.pendidikan[k]);
        new Chart(ctx6, {
            type: 'bar',
            data: {
                labels: keys,
                datasets: [{
                    label: 'Jumlah',
                    data: values,
                    backgroundColor: '#8B5CF680',
                    borderColor: '#8B5CF6',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // ==========================================
    // 7. Pekerjaan (Bar Chart)
    // ==========================================
    const ctx7 = document.getElementById('pekerjaanChart');
    if (ctx7 && demografi.pekerjaan) {
        const keys = Object.keys(demografi.pekerjaan);
        const values = keys.map(k => demografi.pekerjaan[k]);
        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316'];
        new Chart(ctx7, {
            type: 'bar',
            data: {
                labels: keys,
                datasets: [{
                    label: 'Jumlah',
                    data: values,
                    backgroundColor: colors.slice(0, keys.length).map(c => c + '80'),
                    borderColor: colors.slice(0, keys.length),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
});
</script>
@endpush
@endsection