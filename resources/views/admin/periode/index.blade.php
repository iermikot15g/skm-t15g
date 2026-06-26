@extends('layouts.admin')

@section('title', 'Manajemen Periode Survei')
@section('page-title', 'Manajemen Periode Survei')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500">Kelola periode survei yang aktif</p>
        <a href="{{ route('admin.periode.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            + Tambah Periode
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Periode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($periodes as $periode)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $periode->nama_periode }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $periode->tanggal_mulai->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $periode->tanggal_selesai->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($periode->is_active && now()->between($periode->tanggal_mulai, $periode->tanggal_selesai))
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                            @elseif($periode->is_active && now()->gt($periode->tanggal_selesai))
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Kadaluarsa</span>
                            @elseif($periode->is_active && now()->lt($periode->tanggal_mulai))
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Akan Datang</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $periode->creator->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.periode.edit', $periode) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('admin.periode.toggle', $periode) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                                        {{ $periode->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.periode.destroy', $periode) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus periode ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data periode</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection