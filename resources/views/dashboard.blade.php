<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        @foreach([
            ['label'=>'Total Grup','value'=>auth()->user()->groups()->count(),'color'=>'indigo','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
            ['label'=>'Total Tagihan','value'=>\App\Models\Bill::whereIn('group_id',auth()->user()->groups->pluck('id'))->count(),'color'=>'teal','icon'=>'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['label'=>'Anggota Aktif','value'=>auth()->user()->groups->sum(fn($g)=>$g->members_count),'color'=>'purple','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ] as $stat)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-{{ $stat['color'] }}-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</div>
                <div class="text-sm text-gray-500">{{ $stat['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent groups -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Grup Terbaru</h2>
                <a href="{{ route('groups.create') }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg font-medium transition">+ Buat Grup</a>
            </div>
            @forelse($groups as $group)
            <a href="{{ route('groups.show', $group) }}" class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-700 font-bold text-sm">{{ strtoupper(substr($group->name, 0, 2)) }}</div>
                    <div>
                        <div class="font-medium text-sm text-gray-900">{{ $group->name }}</div>
                        <div class="text-xs text-gray-400">{{ $group->members_count }} anggota</div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @empty
            <p class="text-sm text-gray-400 text-center py-8">Belum ada grup. <a href="{{ route('groups.create') }}" class="text-indigo-600 hover:underline">Buat grup pertama</a></p>
            @endforelse
        </div>

        <!-- Recent bills -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Tagihan Terbaru</h2>
            @forelse($recentBills as $bill)
            <a href="{{ route('bills.show', $bill) }}" class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition">
                <div>
                    <div class="font-medium text-sm text-gray-900">{{ $bill->name }}</div>
                    <div class="text-xs text-gray-400">{{ $bill->group->name }} · {{ $bill->date->format('d M Y') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $bill->status === 'calculated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $bill->status }}</span>
                </div>
            </a>
            @empty
            <p class="text-sm text-gray-400 text-center py-8">Belum ada tagihan.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
