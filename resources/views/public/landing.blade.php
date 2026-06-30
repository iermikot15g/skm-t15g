{{-- resources/views/public/landing.blade.php --}}
@extends('layouts.app')

@section('title', 'Survei Kepuasan Masyarakat - Kabupaten Sumenep')

@section('content')
<div class="relative bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    {{-- Logo di landing page --}}
                    <div class="flex justify-center lg:justify-start mb-6">
                        <img src="{{ asset('images/logo-sumenep.png') }}" 
                             alt="Logo Kabupaten Sumenep" 
                             class="h-20 w-auto object-contain">
                    </div>
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block xl:inline">Survei Kepuasan</span>
                        <span class="block text-blue-600 xl:inline">Masyarakat</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Kabupaten Sumenep berkomitmen untuk terus meningkatkan kualitas pelayanan publik.
                        Suara Anda sangat berarti bagi kami.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <a href="{{ route('survey.opd') }}" 
                               class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                                Mulai Isi Survei
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
@endsection