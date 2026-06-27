@extends('layouts.admin')

@section('title', 'Edit Layanan')
@section('page-title', 'Edit Layanan - ' . auth()->user()->opd->nama_opd)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.opd.layanan.update', $layanan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Kode Layanan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode_layanan" value="{{ old('kode_layanan', $layanan->kode_layanan) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('kode_layanan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Nama Layanan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_layanan" value="{{ old('nama_layanan', $layanan->nama_layanan) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nama_layanan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('deskripsi', $layanan->deskripsi) }}</textarea>
                    @error('deskripsi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ $layanan->is_active ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label class="ml-2 block text-sm text-gray-700">Aktif</label>
                </div>

                @if($layanan->survei_responses_count > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm text-yellow-700">
                            ⚠️ Layanan ini memiliki <strong>{{ $layanan->survei_responses_count }}</strong> data survei. 
                            Tidak dapat dihapus, hanya bisa dinonaktifkan.
                        </p>
                    </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.opd.layanan.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection