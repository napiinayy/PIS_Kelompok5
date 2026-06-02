<x-app-layout>
    <x-slot name="title">Template Tagihan</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Template Tagihan</h1>
            <p class="text-sm text-gray-500 mt-1">Simpan tagihan yang sering diulang agar lebih cepat</p>
        </div>
        <a href="{{ route('templates.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
            + Buat Template
        </a>
    </div>

    @if($templates->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="text-5xl mb-4">📋</div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada template</h3>
        <p class="text-gray-400 text-sm mb-6">Simpan tagihan yang sering kamu buat sebagai template — misalnya makan siang rutin, arisan bulanan, dll.</p>
        <a href="{{ route('templates.create') }}"
            class="inline-block px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
            Buat Template Pertama
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($templates as $template)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-indigo-100 transition">
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">📋</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $template->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $template->items_count }} item
                                @if($template->restaurant_name) · {{ $template->restaurant_name }} @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('templates.edit', $template) }}"
                            class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('templates.destroy', $template) }}"
                            onsubmit="return confirm('Hapus template \'{{ $template->name }}\'?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Estimasi total</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($template->estimated_total, 0, ',', '.') }}</span>
                    </div>
                    @if($template->tax_percent > 0 || $template->service_percent > 0)
                    <div class="text-xs text-gray-400">
                        Subtotal: Rp {{ number_format($template->subtotal, 0, ',', '.') }}
                        @if($template->tax_percent > 0) + Pajak {{ $template->tax_percent }}% @endif
                        @if($template->service_percent > 0) + Service {{ $template->service_percent }}% @endif
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1 text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Dipakai {{ $template->times_used }}× kali
                    </div>
                    <a href="{{ route('templates.use', $template) }}"
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition">
                        Gunakan →
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
