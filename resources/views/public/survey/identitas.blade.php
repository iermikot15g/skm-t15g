{{-- resources/views/public/survey/identitas.blade.php --}}
@extends('layouts.app')

@section('title', 'Identitas Responden')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Identitas Responden</h2>
        <span class="text-sm text-gray-500">Langkah 2 dari 3</span>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('survey.store-identity') }}" method="POST">
            @csrf

            <div class="space-y-6" x-data="{ pekerjaanLainnya: false }">
                <!-- Layanan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Layanan yang Dinilai <span class="text-red-500">*</span>
                    </label>
                    <select name="layanan_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Layanan</option>
                        @foreach($layanans as $layanan)
                            <option value="{{ $layanan->id }}" 
                                    {{ old('layanan_id') == $layanan->id ? 'selected' : '' }}>
                                {{ $layanan->nama_layanan }}
                            </option>
                        @endforeach
                    </select>
                    @error('layanan_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIK & Nama -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            NIK <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nik" required maxlength="16"
                               pattern="[0-9]{16}"
                               placeholder="16 digit angka"
                               value="{{ old('nik') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nik')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama" required
                               value="{{ old('nama') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- HP & Usia -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nomor HP <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="hp" required
                               placeholder="08xxxxxxxxxx"
                               value="{{ old('hp') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('hp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Usia <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="usia" required min="1" max="120"
                               value="{{ old('usia') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('usia')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="jenis_kelamin" value="L" 
                                   {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="jenis_kelamin" value="P"
                                   {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                        </label>
                    </div>
                    @error('jenis_kelamin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pendidikan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Pendidikan Terakhir <span class="text-red-500">*</span>
                    </label>
                    <select name="pendidikan" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Pendidikan</option>
                        @foreach(['SD/MI', 'SMP/MTs', 'SMA/MA/SMK', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                            <option value="{{ $pend }}" {{ old('pendidikan') == $pend ? 'selected' : '' }}>
                                {{ $pend }}
                            </option>
                        @endforeach
                    </select>
                    @error('pendidikan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pekerjaan dengan Alpine.js -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Pekerjaan <span class="text-red-500">*</span>
                    </label>
                    <select name="pekerjaan" required 
                            x-model="pekerjaanLainnya"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Pekerjaan</option>
                        @foreach(['PNS', 'TNI/Polri', 'Karyawan Swasta', 'Wiraswasta', 'Petani', 'Nelayan', 'Buruh', 'Pelajar/Mahasiswa', 'Ibu Rumah Tangga', 'Lainnya'] as $kerja)
                            <option value="{{ $kerja }}" {{ old('pekerjaan') == $kerja ? 'selected' : '' }}>
                                {{ $kerja }}
                            </option>
                        @endforeach
                    </select>
                    @error('pekerjaan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pekerjaan Lainnya - Tampil dengan Alpine.js -->
                <div x-show="pekerjaanLainnya === 'Lainnya'" 
                     x-transition:enter.duration.300ms
                     x-transition:leave.duration.300ms
                     class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <label class="block text-sm font-medium text-gray-700">
                        Pekerjaan Lainnya <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="pekerjaan_lainnya"
                           value="{{ old('pekerjaan_lainnya') }}"
                           placeholder="Tulis pekerjaan Anda..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           x-bind:required="pekerjaanLainnya === 'Lainnya'">
                    @error('pekerjaan_lainnya')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">* Wajib diisi jika memilih "Lainnya"</p>
                </div>

                <!-- Submit -->
                <div class="flex justify-end pt-4">
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Lanjut ke Pertanyaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection