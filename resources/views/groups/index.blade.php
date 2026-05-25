<x-app-layout>
    <x-slot name="title">Grup Saya</x-slot>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Grup Saya</h1>
        <a href="{{ route('groups.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">+ Buat Grup</a>
    </div>
    @forelse($groups as $group)
    <a href="{{ route('groups.show', $group) }}" class="block bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4 hover:border-indigo-200 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-700 font-bold text-lg">{{ strtoupper(substr($group->name,0,2)) }}</div>
                <div>
                    <div class="font-semibold text-gray-900">{{ $group->name }}</div>
                    <div class="text-sm text-gray-400">{{ $group->description ?? 'Tidak ada deskripsi' }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $group->members_count }} anggota · Dibuat {{ $group->created_at->diffForHumans() }}</div>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>
    @empty
    <div class="text-center py-24 bg-white rounded-2xl border border-gray-100">
        <div class="text-5xl mb-4">👥</div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada grup</h3>
        <p class="text-gray-400 text-sm mb-6">Buat grup untuk mulai membagi tagihan bersama teman</p>
        <a href="{{ route('groups.create') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">Buat Grup Pertama</a>
    </div>
    @endforelse
</x-app-layout>
