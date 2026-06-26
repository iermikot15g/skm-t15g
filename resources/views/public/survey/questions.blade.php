{{-- resources/views/public/survey/questions.blade.php --}}
@extends('layouts.app')

@section('title', 'Pertanyaan Survei')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Pertanyaan Survei</h2>
        <span class="text-sm text-gray-500">Langkah 3 dari 4</span>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form id="surveyForm" action="{{ route('survey.store-questions') }}" method="POST">
            @csrf

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progress</span>
                    <span id="progressText">Pertanyaan 0 dari 9</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- 9 Unsur Pertanyaan -->
            <div class="space-y-8">
                @foreach($questions as $index => $question)
                    <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                        
                        <!-- Header Unsur -->
                        <div class="flex items-start mb-3">
                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold mr-2 flex-shrink-0">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-blue-600">
                                    UNSUR {{ $question->unsur->kode_unsur }}: {{ $question->unsur->nama_unsur }}
                                </h3>
                                <p class="text-sm text-gray-700 mt-1">{{ $question->pertanyaan }}</p>
                            </div>
                        </div>

                        <!-- Pilihan Jawaban -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-3">
                            @php
                                $skala = json_decode($question->keterangan_skala, true);
                            @endphp
                            @foreach([1, 2, 3, 4] as $nilai)
                                <label class="relative flex items-center p-3 border rounded-lg cursor-pointer hover:bg-blue-50 transition-colors question-option
                                      {{ old('answers.' . $question->id) == $nilai ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           value="{{ $nilai }}"
                                           {{ old('answers.' . $question->id) == $nilai ? 'checked' : '' }}
                                           class="answer-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                           data-question="{{ $question->id }}">
                                    <div class="ml-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $nilai }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $skala[$nilai] ?? '' }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @error('answers.' . $question->id)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <!-- Error Global -->
            <div id="globalError" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                ⚠️ Harap jawab semua pertanyaan sebelum melanjutkan.
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end pt-6 border-t border-gray-200 mt-6">
                <button type="submit"
                        id="submitButton"
                        class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Lanjut ke Kritik & Saran
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('surveyForm');
    const radios = document.querySelectorAll('.answer-radio');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const globalError = document.getElementById('globalError');
    const submitButton = document.getElementById('submitButton');
    const totalQuestions = 9;

    // Fungsi update progress
    function updateProgress() {
        const checked = document.querySelectorAll('.answer-radio:checked').length;
        const percentage = (checked / totalQuestions) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = `Pertanyaan ${checked} dari ${totalQuestions}`;
        
        // Sembunyikan error jika semua sudah dijawab
        if (checked === totalQuestions) {
            globalError.classList.add('hidden');
        }
    }

    // Event listener untuk setiap radio
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Highlight pilihan yang dipilih
            const parentLabel = this.closest('.question-option');
            const siblings = parentLabel.parentElement.querySelectorAll('.question-option');
            siblings.forEach(sibling => {
                sibling.classList.remove('border-blue-500', 'bg-blue-50');
            });
            parentLabel.classList.add('border-blue-500', 'bg-blue-50');
            
            updateProgress();
        });
    });

    // Validasi sebelum submit
    submitButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        const checked = document.querySelectorAll('.answer-radio:checked').length;
        
        if (checked < totalQuestions) {
            globalError.classList.remove('hidden');
            // Scroll ke error
            globalError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        
        // Jika semua sudah dijawab, submit form
        form.submit();
    });

    // Update progress awal
    updateProgress();
});
</script>
@endpush
@endsection