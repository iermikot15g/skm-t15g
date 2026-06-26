{{-- resources/views/public/survey/closed.blade.php --}}
@extends('layouts.app')

@section('title', 'Survei Ditutup')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
        <div class="flex justify-center mb-4">
            <svg class="h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Maaf, Survei Sedang Ditutup</h2>
        <p class="mt-2 text-gray-600">
            Periode survei saat ini sedang tidak aktif. Silakan coba lagi nanti.
        </p>
        <a href="{{ route('home') }}" 
           class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection