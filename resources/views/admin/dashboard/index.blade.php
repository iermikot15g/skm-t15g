{{-- resources/views/admin/dashboard/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Super Admin')
@section('page-title', 'Dashboard Super Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- ========================================== -->
    <!-- FILTER -->
    <!-- ========================================== -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <!-- FORM FILTER -->
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-end gap-4 flex-1">
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
                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        Reset
                    </a>
                </div>
            </form>

            <!-- FORM EXPORT PDF - TERPISAH -->
            <form action="{{ route('admin.dashboard.export-pdf') }}" method="POST" class="flex items-end">
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
    </div>

    <!-- ========================================== -->
    <!-- STATISTIK CARDS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $data['stats']['total_opd'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total OPD</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $data['stats']['total_layanan'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Layanan</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $data['stats']['total_responden'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Responden</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $data['stats']['ikm_overall'] ?? '-' }}</p>
            <p class="text-xs text-gray-500">IKM Keseluruhan</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            @php
                $activePeriode = $data['stats']['active_periode'] ?? null;
            @endphp
            <p class="text-sm font-bold text-indigo-600 truncate">{{ $activePeriode ? $activePeriode->nama_periode : 'Tidak Aktif' }}</p>
            <p class="text-xs text-gray-500">Periode Aktif</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-sm font-bold text-green-600 truncate">{{ $data['top_bottom_opd']['top'][0]['nama_opd'] ?? '-' }}</p>
            <p class="text-xs text-gray-500">OPD Terbaik</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-sm font-bold text-red-600 truncate">{{ $data['top_bottom_opd']['bottom'][0]['nama_opd'] ?? '-' }}</p>
            <p class="text-xs text-gray-500">OPD Terendah</p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRAFIK ROW 1: IKM per OPD + Tren -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- IKM per OPD (Bar Chart) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 IKM per OPD</h3>
            <div class="h-72">
                <canvas id="ikmPerOPDChart"></canvas>
            </div>
        </div>

        <!-- Tren IKM (Multi-Line Chart) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Tren IKM per OPD</h3>
            <div class="h-72">
                <canvas id="trenIKMChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRAFIK ROW 2: Distribusi + Radar -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Distribusi Survei per OPD (Pie Chart) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🍩 Distribusi Survei per OPD</h3>
            <div class="h-72">
                <canvas id="distribusiOPDChart"></canvas>
            </div>
        </div>

        <!-- Per Unsur per OPD (Radar Chart) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🎯 Per Unsur per OPD</h3>
            <div class="h-72">
                <canvas id="unsurPerOPDChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRAFIK ROW 3: Demografi Responden -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Gender -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">👤 Jenis Kelamin</h3>
            <div class="h-48">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
        <!-- Pendidikan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🎓 Pendidikan</h3>
            <div class="h-48">
                <canvas id="pendidikanChart"></canvas>
            </div>
        </div>
        <!-- Pekerjaan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">💼 Pekerjaan</h3>
            <div class="h-48">
                <canvas id="pekerjaanChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TABEL: 5 OPD Terbaik & Terendah -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-green-600 mb-4">🏆 5 OPD Terbaik</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">IKM</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Survei</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data['top_bottom_opd']['top'] ?? [] as $index => $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['nama_opd'] }}</td>
                                <td class="px-4 py-2 text-sm text-center font-semibold text-green-600">{{ $item['ikm'] }}%</td>
                                <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['total_survei'] }}</td>
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

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-red-600 mb-4">⚠️ 5 OPD Terendah</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">IKM</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Survei</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data['top_bottom_opd']['bottom'] ?? [] as $index => $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['nama_opd'] }}</td>
                                <td class="px-4 py-2 text-sm text-center font-semibold text-red-600">{{ $item['ikm'] }}%</td>
                                <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['total_survei'] }}</td>
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

    <!-- ========================================== -->
    <!-- TABEL: Detail Responden Terbaru -->
    <!-- ========================================== -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Detail Responden Terbaru</h3>
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
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
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $item['opd'] }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $item['layanan'] }}</td>
                            <td class="px-4 py-2 text-sm text-center font-semibold">{{ $item['ikm'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $item['tanggal'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-2 text-center text-gray-500">Belum ada data survei</td></tr>
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
        const ikmPerOPD = @json($data['ikm_per_opd'] ?? []);
        const trenData = @json($data['tren_data'] ?? []);
        const distribusiOPD = @json($data['distribusi_opd'] ?? []);
        const unsurPerOPD = @json($data['unsur_per_opd'] ?? []);
        const demografi = @json($data['demografi'] ?? []);

        // ==========================================
        // 1. IKM per OPD (Bar Chart)
        // ==========================================
        const ctx1 = document.getElementById('ikmPerOPDChart');
        if (ctx1 && ikmPerOPD.length > 0) {
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ikmPerOPD.map(item => item.nama_opd),
                    datasets: [{
                        label: 'IKM (%)',
                        data: ikmPerOPD.map(item => item.ikm),
                        backgroundColor: ikmPerOPD.map(item => item.category.color + '80'),
                        borderColor: ikmPerOPD.map(item => item.category.color),
                        borderWidth: 2,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                    }
                }
            });
        }

        // ==========================================
        // 2. Tren IKM (Multi-Line Chart)
        // ==========================================
        const ctx2 = document.getElementById('trenIKMChart');
        if (ctx2 && trenData.length > 0) {
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316'];
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: trenData[0]?.data.map(d => d.periode) || [],
                    datasets: trenData.map((opd, index) => ({
                        label: opd.nama_opd,
                        data: opd.data.map(d => d.ikm),
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
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + (context.parsed.y ?? '-') + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                    }
                }
            });
        }

        // ==========================================
        // 3. Distribusi Survei per OPD (Pie Chart)
        // ==========================================
        const ctx3 = document.getElementById('distribusiOPDChart');
        if (ctx3 && distribusiOPD.length > 0) {
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'];
            new Chart(ctx3, {
                type: 'doughnut',
                data: {
                    labels: distribusiOPD.map(item => item.label),
                    datasets: [{
                        data: distribusiOPD.map(item => item.value),
                        backgroundColor: colors.slice(0, distribusiOPD.length),
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
        // 4. Per Unsur per OPD (Radar Chart)
        // ==========================================
        const ctx4 = document.getElementById('unsurPerOPDChart');
        if (ctx4 && unsurPerOPD.length > 0) {
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
            const labels = unsurPerOPD[0]?.data.map(d => d.unsur) || [];
            new Chart(ctx4, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: unsurPerOPD.slice(0, 5).map((opd, index) => ({
                        label: opd.nama_opd,
                        data: opd.data.map(d => d.nilai),
                        borderColor: colors[index % colors.length],
                        backgroundColor: colors[index % colors.length] + '20',
                        borderWidth: 2,
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
                        r: { min: 1, max: 4, ticks: { stepSize: 0.5 } }
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