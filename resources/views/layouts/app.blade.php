<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Split Bill' }} — Kelompok 5</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#4F46E5', light: '#EEF2FF', dark: '#312E81' },
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet"/>
    <style> body { font-family: 'Inter', sans-serif; } </style>
    @livewireStyles
</head>
<body class="h-full">

<div class="min-h-full">
    <!-- Nav -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-gray-900 text-lg">Split<span class="text-primary">Bill</span></span>
                    </a>
                    <div class="hidden md:flex gap-1">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary-light text-primary font-semibold' : 'text-gray-600 hover:text-gray-900' }}">Dashboard</a>
                        <a href="{{ route('groups.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('groups.*') ? 'bg-primary-light text-primary font-semibold' : 'text-gray-600 hover:text-gray-900' }}">Grup</a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile') }}" class="flex items-center gap-2 text-sm text-gray-700 hover:text-primary">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                        <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-xs text-gray-500 hover:text-red-600 px-2 py-1 rounded border border-gray-200 hover:border-red-200">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
    <div class="bg-green-50 border-b border-green-200 px-4 py-3 text-green-800 text-sm flex items-center gap-2 max-w-7xl mx-auto mt-4 rounded-lg">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-b border-red-200 px-4 py-3 text-red-800 text-sm max-w-7xl mx-auto mt-4 rounded-lg">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>
</div>

@livewireScripts
<script>
// OCR polling
function pollScanStatus(billId, scanId) {
    const interval = setInterval(async () => {
        const res = await fetch(`/bills/${billId}/scan/${scanId}/status`);
        const data = await res.json();
        const el = document.getElementById('scan-status-' + scanId);
        if (!el) { clearInterval(interval); return; }
        if (data.status === 'done') {
            clearInterval(interval);
            el.innerHTML = `<span class="text-green-600 font-medium">✓ Scan selesai — ${data.items.length} item terdeteksi</span>`;
            const btn = document.getElementById('confirm-btn-' + scanId);
            if (btn) btn.classList.remove('hidden');
            // Populate items
            const container = document.getElementById('ocr-items-' + scanId);
            if (container && data.items.length) {
                container.innerHTML = data.items.map((item, i) =>
                    `<div class="flex gap-2 items-center py-1 border-b border-gray-100">
                        <input type="text" name="items[${i}][name]" value="${item.name}" class="flex-1 text-sm border border-gray-200 rounded px-2 py-1">
                        <input type="number" name="items[${i}][price]" value="${item.price}" class="w-28 text-sm border border-gray-200 rounded px-2 py-1">
                        <input type="number" name="items[${i}][quantity]" value="1" class="w-16 text-sm border border-gray-200 rounded px-2 py-1">
                    </div>`
                ).join('');
            }
        } else if (data.status === 'failed') {
            clearInterval(interval);
            el.innerHTML = `<span class="text-red-600">✗ Gagal: ${data.error}</span>`;
        } else {
            el.innerHTML = `<span class="text-yellow-600 animate-pulse">⏳ Memproses...</span>`;
        }
    }, 2000);
}
</script>
</body>
</html>
