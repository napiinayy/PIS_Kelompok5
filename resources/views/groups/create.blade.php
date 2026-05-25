<x-app-layout>
    <x-slot name="title">Buat Grup</x-slot>
    <div class="max-w-lg mx-auto">
        <a href="{{ route('groups.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2 mb-6">Buat Grup Baru</h1>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <form method="POST" action="{{ route('groups.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Grup <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Makan Siang Tim, Liburan Bali..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Opsional — keterangan singkat tentang grup ini"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description') }}</textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('groups.index') }}" class="flex-1 text-center py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50">Batal</a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">Buat Grup</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
