<x-app-layout>
    <x-slot name="title">{{ $template->name }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('templates.index') }}" class="text-indigo-600 hover:underline">Template</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">{{ $template->name }}</span>
        </div>

        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-2xl">📋</div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $template->name }}</h1>
                    <p class="text-sm text-gray-400">
                        {{ $template->items->count() }} item
                        @if($template->restaurant_name) · {{ $template->restaurant_name }} @endif
                        · Dipakai {{ $template->times_used }}× kali
                    </p>
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('templates.edit', $template) }}"
                    class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-sm font-medium rounded-xl transition">Edit</a>
                <a href="{{ route('templates.use', $template) }}"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">Gunakan</a>
            </div>
        </div>

        <!-- Items list -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">Item Pesanan</h2>
            @foreach($template->items as $item)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                    <div class="text-xs text-gray-400">{{ $item->quantity }}× @ Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                </div>
                <div class="font-semibold text-gray-900 text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
            @endforeach

            <!-- Totals -->
            <div class="pt-4 mt-2 border-t border-gray-100 space-y-1.5">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium">Rp {{ number_format($template->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($template->tax_percent > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak ({{ $template->tax_percent }}%)</span>
                    <span class="font-medium">Rp {{ number_format($template->subtotal * $template->tax_percent / 100, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($template->service_percent > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Service ({{ $template->service_percent }}%)</span>
                    <span class="font-medium">Rp {{ number_format($template->subtotal * $template->service_percent / 100, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="font-semibold text-gray-900">Estimasi Total</span>
                    <span class="font-bold text-indigo-700 text-lg">Rp {{ number_format($template->estimated_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($template->notes)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 mb-4">
            <p class="text-sm text-amber-800"><strong>Catatan:</strong> {{ $template->notes }}</p>
        </div>
        @endif

        <div class="flex gap-3">
            <a href="{{ route('templates.use', $template) }}"
                class="flex-1 text-center py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                📋 Gunakan Template Ini
            </a>
            <form method="POST" action="{{ route('templates.destroy', $template) }}"
                onsubmit="return confirm('Hapus template ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="px-4 py-3 border border-red-200 text-red-500 hover:bg-red-50 rounded-xl transition text-sm">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
