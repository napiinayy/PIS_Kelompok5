<x-app-layout>
    <x-slot name="title">Gunakan Template — {{ $template->name }}</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('templates.index') }}" class="text-indigo-600 hover:underline">Template</a>
            <span class="text-gray-400">›</span>
            <a href="{{ route('templates.show', $template) }}" class="text-indigo-600 hover:underline">{{ $template->name }}</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">Gunakan</span>
        </div>

        <!-- Template preview -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-xl">📋</div>
                <div>
                    <div class="font-semibold text-indigo-900">{{ $template->name }}</div>
                    <div class="text-xs text-indigo-500">{{ $template->items->count() }} item · Estimasi Rp {{ number_format($template->estimated_total, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="space-y-1">
                @foreach($template->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-indigo-700">{{ $item->name }} ×{{ $item->quantity }}</span>
                    <span class="text-indigo-900 font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @if($template->tax_percent > 0 || $template->service_percent > 0)
            <div class="mt-2 pt-2 border-t border-indigo-100 text-xs text-indigo-500">
                @if($template->tax_percent > 0) Pajak {{ $template->tax_percent }}% @endif
                @if($template->service_percent > 0) + Service {{ $template->service_percent }}% @endif
                akan ditambahkan
            </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h1 class="text-lg font-bold text-gray-900 mb-1">Buat Tagihan dari Template</h1>
            <p class="text-sm text-gray-400 mb-5">Pilih grup dan tanggal, item akan otomatis terisi dari template.</p>

            @if($groups->isEmpty())
            <div class="text-center py-6">
                <p class="text-gray-400 text-sm mb-4">Kamu belum punya grup. Buat grup dulu untuk menggunakan template.</p>
                <a href="{{ route('groups.create') }}"
                    class="inline-block px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                    Buat Grup
                </a>
            </div>
            @else
            <form method="POST" action="{{ route('templates.apply', $template) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tagihan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Grup <span class="text-red-500">*</span></label>
                    <select name="group_id" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">-- Pilih grup --</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }} ({{ $group->members->count() }} anggota)
                        </option>
                        @endforeach
                    </select>
                    @error('group_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
                    <strong>Yang akan dibuat:</strong>
                    <ul class="mt-2 space-y-1 text-gray-500 text-xs list-disc list-inside">
                        <li>Tagihan baru dengan semua item dari template</li>
                        <li>Semua anggota grup otomatis jadi peserta</li>
                        <li>Kamu bisa edit item setelah dibuat</li>
                    </ul>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('templates.show', $template) }}"
                        class="flex-1 text-center py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                        Buat Tagihan →
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</x-app-layout>
