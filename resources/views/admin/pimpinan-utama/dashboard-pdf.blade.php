{{-- resources/views/admin/pimpinan-utama/dashboard-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a56db; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #1a56db; }
        .header h2 { font-size: 14px; margin: 5px 0; color: #555; }
        .header p { font-size: 10px; color: #777; margin: 2px 0; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 13px; font-weight: bold; background: #f0f4f8; padding: 4px 8px; margin-bottom: 8px; }
        .stats-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
        .stat-card { flex: 1; min-width: 80px; background: #f8fafc; border-radius: 4px; padding: 8px; text-align: center; border: 1px solid #e5e7eb; }
        .stat-card .value { font-size: 16px; font-weight: bold; color: #1a56db; }
        .stat-card .label { font-size: 9px; color: #6b7280; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin: 5px 0; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; font-size: 10px; }
        th { background: #f3f4f6; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 1px 6px; border-radius: 3px; font-size: 8px; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .bar-chart { margin: 5px 0; }
        .bar-row { display: flex; align-items: center; margin: 2px 0; }
        .bar-label { width: 120px; font-size: 9px; }
        .bar-track { flex: 1; background: #e5e7eb; height: 12px; border-radius: 3px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 3px; }
        .bar-value { width: 35px; font-size: 9px; text-align: right; padding-left: 5px; }
        .footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 9px; color: #777; text-align: center; }
        .info-box { display: flex; gap: 20px; margin: 10px 0; }
        .info-item { flex: 1; background: #f8fafc; padding: 8px; border-radius: 4px; border: 1px solid #e5e7eb; }
        .info-item .label { font-size: 9px; color: #6b7280; }
        .info-item .value { font-size: 14px; font-weight: bold; }
        .info-item .value.green { color: #10B981; }
        .info-item .value.red { color: #EF4444; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>PEMERINTAH KABUPATEN SUMENEP</h1>
        <h2>{{ $title }}</h2>
        @if($periode)
            <p>Periode: {{ $periode->nama_periode }} ({{ $periode->tanggal_mulai->format('d/m/Y') }} - {{ $periode->tanggal_selesai->format('d/m/Y') }})</p>
        @else
            <p>Semua Periode</p>
        @endif
        <p>Tanggal Cetak: {{ $generated_at }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="value">{{ count($allStats) }}</div>
            <div class="label">Total OPD</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $totalResponses }}</div>
            <div class="label">Total Survei</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $ikmOverall ?? '-' }}%</div>
            <div class="label">IKM Keseluruhan</div>
        </div>
        <div class="stat-card">
            <div class="value" style="font-size:12px;">
                @php
                    $topOPD = collect($allStats)->filter(function($item) { return $item['ikm'] !== null; })->sortByDesc('ikm.ikm')->first();
                @endphp
                {{ $topOPD['opd']->nama_opd ?? '-' }}
            </div>
            <div class="label">OPD Terbaik</div>
        </div>
        <div class="stat-card">
            <div class="value" style="font-size:12px;">
                @php
                    $bottomOPD = collect($allStats)->filter(function($item) { return $item['ikm'] !== null; })->sortBy('ikm.ikm')->first();
                @endphp
                {{ $bottomOPD['opd']->nama_opd ?? '-' }}
            </div>
            <div class="label">OPD Terendah</div>
        </div>
    </div>

    <!-- IKM per OPD (Bar Chart) -->
    <div class="section">
        <div class="section-title">📊 IKM per OPD</div>
        @if(!empty($chartData['labels']))
            <div class="bar-chart">
                @foreach($chartData['labels'] as $index => $label)
                    <div class="bar-row">
                        <span class="bar-label">{{ $label }}</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $chartData['data'][$index] }}%; background: {{ $chartData['colors'][$index] ?? '#3B82F6' }};"></div>
                        </div>
                        <span class="bar-value">{{ $chartData['data'][$index] }}%</span>
                    </div>
                @endforeach
            </div>
        @else
            <p style="text-align:center;color:#999;font-size:10px;">Belum ada data</p>
        @endif
    </div>

    <!-- Rekap IKM per OPD -->
    <div class="section">
        <div class="section-title">📋 Rekap IKM per OPD</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>OPD</th>
                    <th>Total Survei</th>
                    <th>IKM</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allStats as $index => $stat)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $stat['opd']->nama_opd }}</td>
                        <td class="text-center">{{ $stat['total_responses'] }}</td>
                        <td class="text-center">{{ $stat['ikm'] ? $stat['ikm']['ikm'] . '%' : '-' }}</td>
                        <td class="text-center">
                            @if($stat['ikm'])
                                <span class="badge badge-{{ 
                                    $stat['ikm']['category']['code'] == 'A' ? 'green' : 
                                    ($stat['ikm']['category']['code'] == 'B' ? 'blue' : 
                                    ($stat['ikm']['category']['code'] == 'C' ? 'yellow' : 'red')) 
                                }}">
                                    {{ $stat['ikm']['category']['label'] }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="color:#999;">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        Laporan ini dihasilkan secara otomatis oleh Sistem SKM Kabupaten Sumenep
    </div>

</body>
</html>