{{-- resources/views/admin/laporan/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Laporan Survei')
@section('page-title', 'Laporan Survei')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Periode Survei</label>
                <select name="periode_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Periode</option>
                    @foreach($periodes as $periode)
                        <option value="{{ $periode->id }}" {{ $selectedPeriod == $periode->id ? 'selected' : '' }}>
                            {{ $periode->nama_periode }} ({{ $periode->tanggal_mulai->format('d/m/Y') }} - {{ $periode->tanggal_selesai->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Filter OPD - Hanya untuk Super Admin & Pimpinan Utama --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isPimpinanUtama())
                <div>
                    <label class="block text-sm font-medium text-gray-700">OPD</label>
                    <select name="opd_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ $selectedOPD == $opd->id ? 'selected' : '' }}>
                                {{ $opd->nama_opd }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                {{-- Admin OPD / Pimpinan OPD - hidden input --}}
                <input type="hidden" name="opd_id" value="{{ auth()->user()->opd_id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700">OPD</label>
                    <div class="mt-1 p-2 bg-gray-100 rounded-md text-gray-700 text-sm">
                        {{ auth()->user()->opd->nama_opd ?? 'Tidak terikat OPD' }}
                    </div>
                </div>
            @endif
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tampilkan
                </button>
                <a href="{{ route('admin.laporan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Export Laporan</h3>
                <p class="text-sm text-gray-500">Download laporan dalam format PDF atau Excel</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('admin.laporan.export-pdf') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $selectedPeriod }}">
                    <input type="hidden" name="opd_id" value="{{ $selectedOPD }}">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export PDF
                    </button>
                </form>
                
                <form action="{{ route('admin.laporan.export-excel') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $selectedPeriod }}">
                    <input type="hidden" name="opd_id" value="{{ $selectedOPD }}">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Data -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Preview Data</h3>
            @if(isset($reportData) && count($reportData) > 0)
                <span class="text-sm text-gray-500">Menampilkan {{ count($reportData) }} data</span>
            @endif
        </div>
        
        @if(isset($reportData) && count($reportData) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responden</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IKM</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['responden'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item['layanan'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $item['ikm'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($item['kategori']))
                                        <span class="px-2 py-1 text-xs rounded-full {{ $item['kategori']['bg_color'] ?? 'bg-gray-100' }} {{ $item['kategori']['text_color'] ?? 'text-gray-800' }}">
                                            {{ $item['kategori']['label'] ?? '-' }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['tanggal'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-lg font-medium">Belum ada data</p>
                <p class="text-sm mt-1">Pilih filter dan klik <strong>Tampilkan</strong> untuk melihat data</p>
            </div>
        @endif
    </div>
</div>
@endsection