{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin SKM Sumenep')</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-20 bg-black/50 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
             class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto overflow-y-auto">
            
            <div class="flex items-center justify-center h-16 border-b border-gray-200">
                <span class="text-xl font-bold text-blue-600">SKM Sumenep</span>
                <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Admin</span>
            </div>
            
            <nav class="mt-6 px-4 pb-6">
                <div class="space-y-1">
                    
                    <!-- ========================================== -->
                    <!-- ROLE CHECK -->
                    <!-- ========================================== -->
                    @php
                        $user = auth()->user();
                        $isSuperAdmin = $user?->isSuperAdmin();
                        $isAdminOPD = $user?->isAdminOPD();
                        $isPimpinanOPD = $user?->isPimpinanOPD();
                        $isPimpinanUtama = $user?->isPimpinanUtama();
                    @endphp

                    <!-- ========================================== -->
                    <!-- DASHBOARD SUPER ADMIN -->
                    <!-- ========================================== -->
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Dashboard
                        </a>
                    @endif

                    <!-- ========================================== -->
                    <!-- DASHBOARD OPD - Admin OPD & Pimpinan OPD -->
                    <!-- ========================================== -->
                    @if($isAdminOPD || $isPimpinanOPD)
                        <a href="{{ route('admin.opd.dashboard') }}" 
                           class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.opd.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Dashboard OPD
                        </a>
                    @endif

                    <!-- ========================================== -->
                    <!-- DASHBOARD PIMPINAN UTAMA -->
                    <!-- ========================================== -->
                    @if($isPimpinanUtama || $isSuperAdmin)
                        <a href="{{ route('admin.utama.dashboard') }}" 
                           class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.utama.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Dashboard Utama
                        </a>
                    @endif

                    <!-- ========================================== -->
                    <!-- MENU ADMIN OPD - LAYANAN -->
                    <!-- ========================================== -->
                    @if($isAdminOPD)
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen</p>
                            
                            <a href="{{ route('admin.opd.layanan.index') }}" 
                               class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.opd.layanan.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Layanan
                            </a>
                        </div>
                    @endif

                    <!-- ========================================== -->
                    <!-- MENU SUPER ADMIN ONLY -->
                    <!-- ========================================== -->
                    @if($isSuperAdmin)
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</p>
                            
                            <a href="{{ route('admin.opd.index') }}" 
                               class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.opd.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                OPD
                            </a>
                            
                            <a href="{{ route('admin.layanan.index') }}" 
                               class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.layanan.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Layanan
                            </a>
                            
                            <a href="{{ route('admin.periode.index') }}" 
                               class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.periode.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Periode Survei
                            </a>
                        </div>
                        
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen User</p>
                            
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Users
                            </a>
                        </div>
                        
                    @endif

                    <!-- ========================================== -->
                    <!-- MENU LAPORAN - SEMUA ROLE -->
                    <!-- ========================================== -->
                    @if($isSuperAdmin || $isAdminOPD || $isPimpinanOPD || $isPimpinanUtama)
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Laporan</p>
                            
                            <a href="{{ route('admin.laporan.index') }}" 
                            class="flex items-center px-4 py-3 text-sm rounded-lg {{ request()->routeIs('admin.laporan.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Laporan Survei
                            </a>
                        </div>
                    @endif

                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <h1 class="text-lg font-semibold text-gray-800 hidden sm:block">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700 hidden sm:block">
                            {{ auth()->user()->name }}
                        </span>
                        <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded hidden sm:block">
                            {{ auth()->user()->role->display_name }}
                        </span>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-gray-100">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>