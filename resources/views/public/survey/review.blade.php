{{-- resources/views/public/survey/review.blade.php --}}
@extends('layouts.app')

@section('title', 'Review Jawaban')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Review Jawaban Anda</h2>
        <span class="text-sm text-gray-500">Langkah Terakhir</span>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <!-- Informasi Responden -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Informasi Responden</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div><span class="text-gray-500">Nama:</span> {{ $identity['nama'] }}</div>
                <div><span class="text-gray-500">NIK:</span> {{ substr($identity['nik'], 0, 4) . '****' . substr($identity['nik'], -4) }}</div>
                <div><span class="text-gray-500">Usia:</span> {{ $identity['usia'] }} tahun</div>
                <div><span class="text-gray-500">Jenis Kelamin:</span> {{ $identity['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                <div><span class="text-gray-500">Pendidikan:</span> {{ $identity['pendidikan'] }}</div>
                <div><span class="text-gray-500">Pekerjaan:</span> {{ $identity['pekerjaan'] == 'Lainnya' ? $identity['pekerjaan_lainnya'] : $identity['pekerjaan'] }}</div>
            </div>
        </div>

        <!-- Jawaban -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Jawaban Anda</h3>
            <div class="space-y-3">
                @foreach($questions as $index => $question)
                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold mr-3 flex-shrink-0">
                            {{ $loop->iteration }}
                        </span>
                        <div class="flex-1">
                            <p class="text-sm text-gray-700">{{ $question->pertanyaan }}</p>
                            <p class="text-sm font-medium text-blue-600 mt-1">
                                Nilai: {{ $answers[$question->id] ?? '-' }}
                                <span class="text-gray-500 text-xs">
                                    ({{ json_decode($question->keterangan_skala, true)[$answers[$question->id]] ?? '' }})
                                </span>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kritik & Saran -->
        @if($kritikSaran)
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Kritik & Saran</h3>
                <p class="text-sm text-gray-700">{{ $kritikSaran }}</p>
            </div>
        @endif

        <!-- Tombol -->
        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <a href="{{ route('survey.kritik-saran') }}" 
               class="px-4 py-2 text-gray-600 hover:text-gray-800">
                ← Kembali
            </a>
            <form action="{{ route('survey.submit') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    ✅ Kirim Survei
                </button>
            </form>
        </div>
    </div>
</div>
@endsection