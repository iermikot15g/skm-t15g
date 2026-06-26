{{-- resources/views/public/survey/opd.blade.php --}}
@extends('layouts.app')

@section('title', 'Pilih Unit Layanan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Pilih Unit Layanan</h2>
        <span class="text-sm text-gray-500">Langkah 1 dari 3</span>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($opds->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
            Belum ada unit layanan yang tersedia.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($opds as $opd)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                    <form action="{{ route('survey.identity') }}" method="POST">
                        @csrf
                        <input type="hidden" name="opd_id" value="{{ $opd->id }}">
                        <button type="submit" class="w-full text-left p-6">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-100 text-blue-600 mb-4">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $opd->nama_opd }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $opd->layanans->count() }} layanan tersedia
                            </p>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection