<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Split Bill' }} — Kelompok 5</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet"/>
    <style>body { font-family: 'Inter', sans-serif; }</style>
    @livewireStyles
</head>
<body class="h-full">

<div class="min-h-full">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left: Logo + links -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-gray-900 text-lg">Split<span class="text-indigo-600">Bill</span></span>
                    </a>

                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('dashboard') }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('groups.index') }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs('groups.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            Grup
                        </a>

                        <!-- Categories dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open"
                                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition
                                {{ request()->routeIs('categories.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                🏷️ Kategori
                                <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition
                                class="absolute left-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                                <a href="{{ route('categories.dashboard') }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                    <span>📊</span> Dashboard Pengeluaran
                                </a>
                                <a href="{{ route('categories.index') }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                    <span>🏷️</span> Kelola Kategori
                                </a>
                                <div class="border-t border-gray-50 my-1"></div>
                                <a href="{{ route('categories.create') }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                    <span>+</span> Buat Kategori Baru
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('templates.index') }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs('templates.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            📋 Template
                        </a>
                    </div>
                </div>

                <!-- Right: User + logout -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile') }}"
                        class="flex items-center gap-2 text-sm text-gray-700 hover:text-indigo-600 transition">
                        <img src="{{ auth()->user()->avatar_url }}"
                            class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="">
                        <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-xs text-gray-500 hover:text-red-600 px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-red-200 transition">
                            Logout
                        </button>
                    </form>

                    <!-- Mobile menu button -->
                    <button onclick="toggleMobileMenu()"
                        class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-3 space-y-1 border-t border-gray-100 pt-2">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600' }}">Dashboard</a>
                <a href="{{ route('groups.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('groups.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600' }}">Grup</a>
                <a href="{{ route('categories.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('categories.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600' }}">📊 Dashboard Pengeluaran</a>
                <a href="{{ route('categories.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('categories.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600' }}">🏷️ Kelola Kategori</a>
                <a href="{{ route('templates.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('templates.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600' }}">📋 Template</a>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-green-800 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-800 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Page content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>
</div>

@livewireScripts

<script>
function toggleMobileMenu() {
    document.getElementById('mobile-menu').classList.toggle('hidden');
}

// Used by bills/items.blade.php for OCR polling
function startPolling(billId, scanId) {
    const interval = setInterval(async () => {
        try {
            const res  = await fetch(`/bills/${billId}/scan/${scanId}/status`);
            const data = await res.json();
            if (data.status === 'done' || data.status === 'failed') {
                clearInterval(interval);
                location.reload();
            }
        } catch (e) {
            clearInterval(interval);
            location.reload();
        }
    }, 2500);
}
</script>

<!-- Alpine.js for dropdown -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
