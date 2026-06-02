<x-app-layout>
    <x-slot name="title">Buat Kategori</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:underline">Kategori</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">Buat Baru</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h1 class="text-lg font-bold text-gray-900 mb-5">Buat Kategori Baru</h1>

            <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
                @csrf

                <!-- Preview -->
                <div class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 bg-gray-50">
                    <div id="preview-icon"
                        class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl transition-all"
                        style="background-color: #4F46E522">
                        🏷️
                    </div>
                    <div>
                        <div id="preview-name" class="font-semibold text-gray-900 text-base">Nama Kategori</div>
                        <div class="text-xs text-gray-400 mt-0.5">Preview tampilan kategori</div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="input-name" value="{{ old('name') }}" required
                        placeholder="Contoh: Makan, Transport, Belanja..."
                        oninput="document.getElementById('preview-name').textContent = this.value || 'Nama Kategori'"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon Emoji <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-8 gap-2 mb-2">
                        @foreach(['🍽️','🚗','🛒','🎉','💊','📚','🏠','✈️','🎬','🏋️','💼','🐾','🎮','💅','⚡','🌿'] as $emoji)
                        <button type="button"
                            onclick="selectIcon('{{ $emoji }}')"
                            class="emoji-btn w-10 h-10 rounded-lg border-2 border-gray-100 hover:border-indigo-300 flex items-center justify-center text-xl transition"
                            data-emoji="{{ $emoji }}">
                            {{ $emoji }}
                        </button>
                        @endforeach
                    </div>
                    <input type="text" name="icon" id="icon-input" value="{{ old('icon', '🏷️') }}" required
                        placeholder="Atau ketik emoji sendiri"
                        oninput="document.getElementById('preview-icon').textContent = this.value || '🏷️'"
                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('icon')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach(['#4F46E5','#0D9488','#DC2626','#D97706','#16A34A','#7C3AED','#0284C7','#DB2777','#374151','#EA580C'] as $color)
                        <button type="button"
                            onclick="selectColor('{{ $color }}')"
                            class="color-btn w-8 h-8 rounded-full border-2 border-white shadow-md hover:scale-110 transition-transform"
                            style="background-color: {{ $color }}"
                            data-color="{{ $color }}">
                        </button>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="color" id="color-picker" value="{{ old('color', '#4F46E5') }}"
                            oninput="selectColor(this.value)"
                            class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" name="color" id="color-input" value="{{ old('color', '#4F46E5') }}" required
                            placeholder="#4F46E5"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    @error('color')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Opsional — keterangan singkat kategori ini"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('categories.index') }}"
                        class="flex-1 text-center py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                        Buat Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
function selectColor(color) {
    document.getElementById('color-input').value = color;
    document.getElementById('color-picker').value = color;
    document.getElementById('preview-icon').style.backgroundColor = color + '22';
    document.querySelector('#preview-icon').style.backgroundColor = color + '33';
}

function selectIcon(emoji) {
    document.getElementById('icon-input').value = emoji;
    document.getElementById('preview-icon').textContent = emoji;
    // Highlight selected
    document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('border-indigo-400','bg-indigo-50'));
    event.target.closest('.emoji-btn').classList.add('border-indigo-400','bg-indigo-50');
}
</script>
