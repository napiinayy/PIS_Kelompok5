<x-app-layout>
    <x-slot name="title">{{ $category->name }}</x-slot>

    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('categories.dashboard') }}" class="text-sm text-indigo-600 hover:underline">← Dashboard</a>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
                style="background-color: {{ $category->color }}33">
                {{ $category->icon }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h1>
                @if($category->description)
                <p class="text-sm text-gray-500">{{ $category->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('categories.edit', $category) }}"
                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-sm font-medium rounded-xl transition">
                Edit
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $bills->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">Total Tagihan</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
            <div class="text-xl font-bold text-indigo-700">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">Total Pengeluaran</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center sm:col-span-1 col-span-2">
            <div class="text-xl font-bold text-gray-900">
                {{ $bills->count() > 0 ? 'Rp ' . number_format($totalSpent / $bills->count(), 0, ',', '.') : '-' }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Rata-rata per Tagihan</div>
        </div>
    </div>

    <!-- Bills list -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Tagihan dalam Kategori Ini</h2>

        @forelse($bills as $bill)
        @php
            $billRoute = $bill->status === 'settled'
                ? route('bills.show', $bill)
                : ($bill->status === 'calculated' ? route('bills.split.page', $bill) : route('bills.items.page', $bill));
        @endphp
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm flex-shrink-0"
                    style="background-color: {{ $category->color }}22">
                    {{ $category->icon }}
                </div>
                <div>
                    <a href="{{ $billRoute }}"
                        class="font-medium text-sm text-gray-900 hover:text-indigo-600">
                        {{ $bill->name }}
                    </a>
                    <div class="text-xs text-gray-400">
                        {{ $bill->group->name }} · {{ $bill->date->format('d M Y') }}
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                <div class="text-right">
                    <div class="font-semibold text-gray-900 text-sm">
                        Rp {{ number_format($bill->grand_total, 0, ',', '.') }}
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $bill->status === 'settled'    ? 'bg-gray-100 text-gray-500'   :
                           ($bill->status === 'calculated' ? 'bg-green-100 text-green-700' :
                                                             'bg-yellow-100 text-yellow-700') }}">
                        {{ ['draft'=>'Draft','calculated'=>'Terhitung','settled'=>'Selesai'][$bill->status] }}
                    </span>
                </div>
                <form method="POST"
                    action="{{ route('categories.bills.detach', [$category, $bill]) }}"
                    class="inline">
                    @csrf @method('DELETE')
                    <button class="text-gray-300 hover:text-red-400 p-1 transition"
                        title="Hapus dari kategori ini">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-400 text-center py-8">
            Belum ada tagihan di kategori ini.<br>
            <span class="text-xs">Tag tagihan dari halaman detail tagihan untuk menambahkan.</span>
        </p>
        @endforelse
    </div>
</x-app-layout>
