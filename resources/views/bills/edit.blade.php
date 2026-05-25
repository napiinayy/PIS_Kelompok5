<x-app-layout>
    <x-slot name="title">Edit Tagihan</x-slot>

    <div class="max-w-lg mx-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('groups.show', $bill->group) }}" class="text-indigo-600 hover:underline">{{ $bill->group->name }}</a>
            <span class="text-gray-400">›</span>
            <a href="{{ route('bills.items.page', $bill) }}" class="text-indigo-600 hover:underline">{{ $bill->name }}</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">Edit</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h1 class="text-lg font-bold text-gray-900 mb-5">Edit Detail Tagihan</h1>

            <form method="POST" action="{{ route('bills.update', $bill) }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tagihan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $bill->name) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Restoran / Tempat</label>
                    <input type="text" name="restaurant_name" value="{{ old('restaurant_name', $bill->restaurant_name) }}"
                        placeholder="Opsional"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', $bill->date->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pajak (%)</label>
                        <input type="number" name="tax_percent" value="{{ old('tax_percent', $bill->tax_percent) }}"
                            min="0" max="100" step="0.5"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service (%)</label>
                        <input type="number" name="service_percent" value="{{ old('service_percent', $bill->service_percent) }}"
                            min="0" max="100" step="0.5"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes', $bill->notes) }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('bills.items.page', $bill) }}"
                        class="flex-1 text-center py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <!-- Danger zone -->
            <div class="pt-5 mt-5 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-3">Zona Berbahaya</p>
                <form method="POST" action="{{ route('bills.destroy', $bill) }}"
                    onsubmit="return confirm('Yakin hapus tagihan \'{{ $bill->name }}\'? Semua item dan data pembagian akan ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full py-2.5 border border-red-200 text-red-500 hover:bg-red-50 text-sm rounded-xl transition">
                        🗑️ Hapus Tagihan Ini
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
