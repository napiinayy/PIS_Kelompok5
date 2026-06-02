<x-app-layout>
    <x-slot name="title">Kategori Pengeluaran</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kategori Pengeluaran</h1>
            <p class="text-sm text-gray-500 mt-1">Kelompokkan tagihan berdasarkan jenis pengeluaran</p>
        </div>
        <a href="{{ route('categories.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
            + Buat Kategori
        </a>
    </div>

    @if($categories->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="text-5xl mb-4">🏷️</div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada kategori</h3>
        <p class="text-gray-400 text-sm mb-6">Buat kategori untuk mengelompokkan tagihan seperti Makan, Transport, Belanja, dll.</p>
        <a href="{{ route('categories.create') }}"
            class="inline-block px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
            Buat Kategori Pertama
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $category)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-indigo-100 transition">
            <!-- Color header -->
            <div class="h-2" style="background-color: {{ $category->color }}"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl"
                            style="background-color: {{ $category->color }}22">
                            {{ $category->icon }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $category->bills_count }} tagihan</div>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('categories.edit', $category) }}"
                            class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}"
                            onsubmit="return confirm('Hapus kategori \'{{ $category->name }}\'? Tagihan tidak ikut terhapus.')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                @if($category->description)
                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $category->description }}</p>
                @endif

                <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                    <div>
                        <div class="text-xs text-gray-400">Total pengeluaran</div>
                        <div class="font-semibold text-gray-900 text-sm">
                            Rp {{ number_format($category->total_spent, 0, ',', '.') }}
                        </div>
                    </div>
                    <a href="{{ route('categories.show', $category) }}"
                        class="text-xs text-indigo-600 hover:underline font-medium">
                        Lihat tagihan →
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
