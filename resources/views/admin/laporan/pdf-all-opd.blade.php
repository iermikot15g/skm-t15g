<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SKM - Semua OPD</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header h2 { font-size: 14px; margin: 5px 0; color: #555; }
        .header p { font-size: 11px; color: #777; margin: 2px 0; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 13px; font-weight: bold; background: #f0f0f0; padding: 5px 10px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; font-size: 10px; }
        td { font-size: 10px; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #777; text-align: center; }
        .badge { padding: 1px 6px; border-radius: 3px; font-size: 9px; }
        .badge-green { background: #d4edda; color: #155724; }
        .badge-blue { background: #d1ecf1; color: #0c5460; }
        .badge-yellow { background: #fff3cd; color: #856404; }
        .badge-red { background: #f8d7da; color: #721c24; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN SURVEI KEPUASAN MASYARAKAT</h1>
        <h2>REKAPITULASI SELURUH OPD</h2>
        @if($periode)
            <p>Periode: {{ $periode->nama_periode }} ({{ $periode->tanggal_mulai->format('d/m/Y') }} - {{ $periode->tanggal_selesai->format('d/m/Y') }})</p>
        @else
            <p>Semua Periode</p>
        @endif
        <p>Tanggal Cetak: {{ $generated_at }}</p>
    </div>

    <!-- Rekap per OPD -->
    <div class="section">
        <div class="section-title">Rekapitulasi IKM per OPD</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>OPD</th>
                    <th>Total Survei</th>
                    <th>IKM (%)</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allData as $index => $data)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $data['opd']->nama_opd }}</td>
                        <td class="text-center">{{ $data['ikmData'] ? $data['ikmData']['total_responden'] : 0 }}</td>
                        <td class="text-center">{{ $data['ikmData'] ? $data['ikmData']['ikm'] : '-' }}</td>
                        <td class="text-center">
                            @if($data['ikmData'])
                                <span class="badge badge-{{ 
                                    $data['ikmData']['category']['code'] == 'A' ? 'green' : 
                                    ($data['ikmData']['category']['code'] == 'B' ? 'blue' : 
                                    ($data['ikmData']['category']['code'] == 'C' ? 'yellow' : 'red')) 
                                }}">
                                    {{ $data['ikmData']['category']['label'] }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Detail per OPD -->
    @foreach($allData as $data)
        <div class="page-break"></div>
        <div class="section">
            <div class="section-title">{{ $data['opd']->nama_opd }}</div>
            
            @if($data['ikmData'])
            <div style="margin-bottom:10px;font-size:11px;">
                <strong>IKM:</strong> {{ $data['ikmData']['ikm'] }}% 
                <span class="badge badge-{{ 
                    $data['ikmData']['category']['code'] == 'A' ? 'green' : 
                    ($data['ikmData']['category']['code'] == 'B' ? 'blue' : 
                    ($data['ikmData']['category']['code'] == 'C' ? 'yellow' : 'red')) 
                }}">
                    {{ $data['ikmData']['category']['label'] }}
                </span>
                | Total Responden: {{ $data['ikmData']['total_responden'] }}
            </div>
            @endif

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Usia</th>
                        <th>JK</th>
                        <th>Pendidikan</th>
                        <th>Pekerjaan</th>
                        <th>Layanan</th>
                        <th>IKM</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['responses'] as $index => $response)
                        @php
                            $nilai = $response->jawabans->pluck('nilai')->toArray();
                            $ikm = count($nilai) === 9 ? round((array_sum($nilai) / 9) * 25, 2) : '-';
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $response->responden->nama }}</td>
                            <td>{{ $response->responden->usia }}</td>
                            <td>{{ $response->responden->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                            <td>{{ $response->responden->pendidikan }}</td>
                            <td>{{ $response->responden->pekerjaan }}</td>
                            <td>{{ $response->layanan->nama_layanan }}</td>
                            <td class="text-center">{{ $ikm }}</td>
                            <td>{{ $response->submitted_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center" style="color:#999;">Belum ada data survei</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        Laporan ini dihasilkan secara otomatis oleh Sistem SKM Kabupaten Sumenep
    </div>

</body>
</html>