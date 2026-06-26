{{-- resources/views/public/survey/questions-placeholder.blade.php --}}
@extends('layouts.app')

@section('title', 'Pertanyaan Survei')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-800">✅ FASE 2 Selesai!</h2>
        <p class="mt-4 text-gray-600">
            Form identitas berhasil disimpan. <br>
            <span class="text-sm text-gray-500">Data tersimpan di session:</span>
        </p>
        
        <div class="mt-4 text-left bg-gray-50 p-4 rounded">
            <pre class="text-xs">{{ json_encode(session('survey_identity'), JSON_PRETTY_PRINT) }}</pre>
        </div>
        
        <div class="mt-6 flex justify-center space-x-4">
            <a href="{{ route('survey.opd') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Kembali
            </a>
            <a href="{{ route('home') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection