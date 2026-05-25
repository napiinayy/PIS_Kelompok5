<x-app-layout>
    <x-slot name="title">Buat Tagihan Baru</x-slot>

    <div class="max-w-lg mx-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('groups.index') }}" class="text-indigo-600 hover:underline">Grup</a>
            <span class="text-gray-400">›</span>
            <a href="{{ route('groups.show', $group) }}" class="text-indigo-600 hover:underline">{{ $group->name }}</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">Buat Tagihan</span>
        </div>

        <!-- Progress bar -->
        <div class="flex items-center gap-0 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold">1</div>
                <span class="text-sm font-semibold text-indigo-700 hidden sm:block">Buat Tagihan</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-sm font-bold">2</div>
                <span class="text-sm text-gray-400 hidden sm:block">Tambah Item</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-sm font-bold">3</div>
                <span class="text-sm text-gray-400 hidden sm:block">Bagi Tagihan</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h1 class="text-lg font-bold text-gray-900 mb-5">Detail Tagihan</h1>

            <form method="POST" action="{{ route('bills.store', $group) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Tagihan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: Makan Siang 20 Mei"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Restoran / Tempat</label>
                    <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}"
                        placeholder="Opsional — contoh: Warteg Barokah"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pajak (%)</label>
                        <input type="number" name="tax_percent" value="{{ old('tax_percent', 0) }}"
                            min="0" max="100" step="0.5" placeholder="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-400 mt-1">Contoh: 10 untuk 10%</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service (%)</label>
                        <input type="number" name="service_percent" value="{{ old('service_percent', 0) }}"
                            min="0" max="100" step="0.5" placeholder="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-400 mt-1">Contoh: 5 untuk 5%</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Opsional"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="bg-indigo-50 rounded-xl p-4 text-sm text-indigo-700 border border-indigo-100">
                    <strong>Info:</strong> Semua {{ $group->members->count() }} anggota grup
                    ({{ $group->members->pluck('name')->join(', ') }})
                    akan otomatis ditambahkan sebagai peserta.
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('groups.show', $group) }}"
                        class="flex-1 text-center py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                        Buat & Tambah Item →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
