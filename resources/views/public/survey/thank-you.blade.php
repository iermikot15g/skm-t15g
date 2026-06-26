{{-- resources/views/public/survey/thank-you.blade.php --}}
@extends('layouts.app')

@section('title', 'Terima Kasih')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <!-- Icon -->
        <div class="flex justify-center mb-4">
            <div class="h-20 w-20 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <h2 class="text-3xl font-bold text-gray-800">Terima Kasih! 🎉</h2>
        <p class="mt-2 text-gray-600">
            Survei Anda telah berhasil kami terima.
        </p>
        <p class="mt-1 text-sm text-gray-500">
            Masukan Anda sangat berarti untuk peningkatan pelayanan publik di Kabupaten Sumenep.
        </p>

        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500">Kode Referensi</p>
            <p class="text-xl font-mono font-bold text-blue-600">SKM-2026-001234</p>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('survey.opd') }}" 
               class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                Isi Survei Lain
            </a>
            <a href="{{ route('home') }}" 
               class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection