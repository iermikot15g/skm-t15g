{{-- resources/views/public/survey/kritik-saran.blade.php --}}
@extends('layouts.app')

@section('title', 'Kritik & Saran')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Kritik & Saran</h2>
        <span class="text-sm text-gray-500">Langkah 4 dari 4</span>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('survey.store-kritik-saran') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700">
                        💡 Kritik dan saran Anda sangat berharga untuk meningkatkan kualitas pelayanan publik.
                    </p>
                </div>

                <!-- Kritik & Saran -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Kritik & Saran <span class="text-gray-400">(opsional)</span>
                    </label>
                    <textarea name="kritik_saran" rows="5"
                              placeholder="Tulis kritik, saran, atau masukan Anda untuk perbaikan pelayanan..."
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('kritik_saran') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Maksimal 1000 karakter</p>
                    @error('kritik_saran')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <a href="{{ route('survey.questions') }}" 
                       class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        ← Kembali ke Pertanyaan
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Review Jawaban
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection