<x-app-layout>
    <x-slot name="title">Buat Template</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('templates.index') }}" class="text-indigo-600 hover:underline">Template</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">Buat Baru</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h1 class="text-lg font-bold text-gray-900 mb-5">Buat Template Tagihan</h1>

            <form method="POST" action="{{ route('templates.store') }}" id="template-form" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Template <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Contoh: Makan Siang Rutin"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Restoran</label>
                        <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}"
                            placeholder="Opsional"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pajak (%)</label>
                        <input type="number" name="tax_percent" value="{{ old('tax_percent', 0) }}"
                            min="0" max="100" step="0.5"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service (%)</label>
                        <input type="number" name="service_percent" value="{{ old('service_percent', 0) }}"
                            min="0" max="100" step="0.5"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Opsional"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                <!-- Items -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">Item Pesanan <span class="text-red-500">*</span></label>
                        <button type="button" onclick="addItem()"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            + Tambah Item
                        </button>
                    </div>

                    <div id="items-container" class="space-y-2">
                        @if(old('items'))
                            @foreach(old('items') as $i => $item)
                            <div class="flex gap-2 items-center item-row">
                                <input type="text" name="items[{{ $i }}][name]" value="{{ $item['name'] }}" placeholder="Nama item" required
                                    class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                                <input type="number" name="items[{{ $i }}][price]" value="{{ $item['price'] }}" placeholder="Harga" min="0" required
                                    class="w-28 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                                <input type="number" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] }}" min="1" required
                                    class="w-16 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                                <button type="button" onclick="this.closest('.item-row').remove()" class="text-gray-300 hover:text-red-400 p-1 flex-shrink-0">×</button>
                            </div>
                            @endforeach
                        @else
                        <div class="flex gap-2 items-center item-row">
                            <input type="text" name="items[0][name]" placeholder="Nama item" required
                                class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                            <input type="number" name="items[0][price]" placeholder="Harga" min="0" required
                                class="w-28 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                            <input type="number" name="items[0][quantity]" value="1" min="1" required
                                class="w-16 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                            <button type="button" onclick="this.closest('.item-row').remove()" class="text-gray-300 hover:text-red-400 p-1 flex-shrink-0">×</button>
                        </div>
                        @endif
                    </div>
                    <div class="flex gap-2 mt-2 px-1">
                        <span class="flex-1 text-xs text-gray-400">Nama Item</span>
                        <span class="w-28 text-xs text-gray-400">Harga (Rp)</span>
                        <span class="w-16 text-xs text-gray-400">Qty</span>
                        <span class="w-7"></span>
                    </div>
                    @error('items')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('templates.index') }}"
                        class="flex-1 text-center py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
let itemCount = {{ old('items') ? count(old('items')) : 1 }};

function addItem() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-center item-row';
    row.innerHTML = `
        <input type="text"   name="items[${itemCount}][name]"     placeholder="Nama item" required class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="number" name="items[${itemCount}][price]"    placeholder="Harga" min="0" required class="w-28 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="number" name="items[${itemCount}][quantity]" value="1" min="1" required class="w-16 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <button type="button" onclick="this.closest('.item-row').remove()" class="text-gray-300 hover:text-red-400 p-1 flex-shrink-0 text-lg leading-none">×</button>
    `;
    container.appendChild(row);
    itemCount++;
}
</script>
