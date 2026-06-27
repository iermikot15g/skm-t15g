<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SKM - {{ $opd->nama_opd }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header h2 { font-size: 14px; margin: 5px 0; color: #555; }
        .header p { font-size: 11px; color: #777; margin: 2px 0; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; background: #f0f0f0; padding: 5px 10px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; font-size: 11px; }
        td { font-size: 11px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #777; text-align: center; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .badge-green { background: #d4edda; color: #155724; }
        .badge-blue { background: #d1ecf1; color: #0c5460; }
        .badge-yellow { background: #fff3cd; color: #856404; }
        .badge-red { background: #f8d7da; color: #721c24; }
        .ikm-box { text-align: center; padding: 10px; background: #f8f9fa; border-radius: 5px; margin: 10px 0; }
        .ikm-value { font-size: 24px; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN SURVEI KEPUASAN MASYARAKAT</h1>
        <h2>{{ $opd->nama_opd }}</h2>
        @if($periode)
            <p>Periode: {{ $periode->nama_periode }} ({{ $periode->tanggal_mulai->format('d/m/Y') }} - {{ $periode->tanggal_selesai->format('d/m/Y') }})</p>
        @else
            <p>Semua Periode</p>
        @endif
        <p>Tanggal Cetak: {{ $generated_at }}</p>
    </div>

    <!-- IKM Summary -->
    @if($ikmData)
    <div class="ikm-box">
        <div>
            <span style="font-size:14px;font-weight:bold;">IKM: </span>
            <span class="ikm-value">{{ $ikmData['ikm'] }}%</span>
            <span class="badge badge-{{ 
                $ikmData['category']['code'] == 'A' ? 'green' : 
                ($ikmData['category']['code'] == 'B' ? 'blue' : 
                ($ikmData['category']['code'] == 'C' ? 'yellow' : 'red')) 
            }}">
                {{ $ikmData['category']['label'] }}
            </span>
        </div>
        <div style="font-size:11px;color:#555;margin-top:5px;">
            Total Responden: {{ $ikmData['total_responden'] }}
        </div>
    </div>
    @endif

    <!-- Data Responden -->
    <div class="section">
        <div class="section-title">Data Responden</div>
        @if($responses->isEmpty())
            <p style="text-align:center;color:#999;">Belum ada data survei</p>
        @else
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
                @foreach($responses as $index => $response)
                    @php
                        $nilai = $response->jawabans->pluck('nilai')->toArray();
                        $ikm = count($nilai) === 9 ? round((array_sum($nilai) / 9) * 25, 2) : '-';
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $response->responden->nama }}</td>
                        <td>{{ $response->responden->usia }}</td>
                        <td>{{ $response->responden->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                        <td>{{ $response->responden->pendidikan }}</td>
                        <td>{{ $response->responden->pekerjaan }}</td>
                        <td>{{ $response->layanan->nama_layanan }}</td>
                        <td class="text-center">{{ $ikm }}</td>
                        <td>{{ $response->submitted_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        Laporan ini dihasilkan secara otomatis oleh Sistem SKM Kabupaten Sumenep
    </div>

</body>
</html>