<x-app-layout>
    <x-slot name="title">{{ $group->name }}</x-slot>

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('groups.index') }}" class="text-sm text-indigo-600 hover:underline">← Semua Grup</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $group->name }}</h1>
            @if($group->description)
                <p class="text-gray-500 text-sm mt-1">{{ $group->description }}</p>
            @endif
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('bills.create', $group) }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                + Buat Tagihan
            </a>
            @if($group->isAdmin(auth()->user()))
            <a href="{{ route('groups.edit', $group) }}"
                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-sm font-medium rounded-xl transition">
                Edit
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Bills list -->
        <div class="lg:col-span-2">
            <h2 class="font-semibold text-gray-900 mb-3">Tagihan</h2>

            @forelse($group->bills as $bill)
            @php
                // Route to the right page based on bill status
                if ($bill->status === 'settled') {
                    $billRoute = route('bills.show', $bill);
                } elseif ($bill->status === 'calculated') {
                    $billRoute = route('bills.split.page', $bill);
                } else {
                    $billRoute = route('bills.items.page', $bill);
                }
            @endphp
            <a href="{{ $billRoute }}"
                class="block bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-3 hover:border-indigo-200 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $bill->name }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            {{ $bill->restaurant_name ? $bill->restaurant_name . ' · ' : '' }}
                            {{ $bill->date->format('d M Y') }} ·
                            {{ $bill->items_count }} item
                        </div>
                        <!-- Show which step the bill is on -->
                        <div class="text-xs mt-1.5">
                            @if($bill->status === 'settled')
                                <span class="text-gray-400">✓ Selesai & ditutup</span>
                            @elseif($bill->status === 'calculated')
                                <span class="text-indigo-500">→ Lanjut ke pembagian & pembayaran</span>
                            @else
                                <span class="text-amber-500">→ Tambah item pesanan</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-4">
                        <div class="font-bold text-gray-900">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</div>
                        <span class="text-xs px-2 py-0.5 rounded-full mt-1 inline-block
                            {{ $bill->status === 'calculated' ? 'bg-green-100 text-green-700' :
                               ($bill->status === 'settled'   ? 'bg-gray-100 text-gray-500' :
                                                                'bg-yellow-100 text-yellow-700') }}">
                            {{ ['draft' => 'Draft', 'calculated' => 'Terhitung', 'settled' => 'Selesai'][$bill->status] }}
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
                <div class="text-4xl mb-3">🧾</div>
                <p class="text-gray-500 text-sm">Belum ada tagihan di grup ini</p>
                <a href="{{ route('bills.create', $group) }}"
                    class="inline-block mt-4 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                    Buat Tagihan Pertama
                </a>
            </div>
            @endforelse
        </div>

        <!-- Members sidebar -->
        <div>
            <h2 class="font-semibold text-gray-900 mb-3">Anggota ({{ $group->members->count() }})</h2>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                @foreach($group->members as $member)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-3">
                        <img src="{{ $member->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $member->pivot->role === 'admin' ? '👑 Admin' : 'Member' }}
                            </div>
                        </div>
                    </div>
                    @if($group->isAdmin(auth()->user()) && $member->id !== $group->created_by)
                    <form method="POST" action="{{ route('groups.members.remove', [$group, $member]) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                    </form>
                    @endif
                </div>
                @endforeach

                @if($group->isAdmin(auth()->user()))
                <form method="POST" action="{{ route('groups.invite', $group) }}" class="mt-4 flex gap-2">
                    @csrf
                    <input type="email" name="email" placeholder="Email pengguna" required
                        class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    <button type="submit"
                        class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-xl">
                        Undang
                    </button>
                </form>
                @endif
            </div>

            @if($group->isAdmin(auth()->user()))
            <div class="mt-4">
                <form method="POST" action="{{ route('groups.destroy', $group) }}"
                    onsubmit="return confirm('Hapus grup \'{{ $group->name }}\'? Semua tagihan akan ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full py-2 border border-red-200 text-red-500 hover:bg-red-50 text-sm rounded-xl transition">
                        Hapus Grup
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
