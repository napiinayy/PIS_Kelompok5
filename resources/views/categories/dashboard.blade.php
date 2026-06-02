<x-app-layout>
    <x-slot name="title">Dashboard Pengeluaran</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Pengeluaran</h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan semua pengeluaran berdasarkan kategori</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('categories.index') }}"
                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-sm font-medium rounded-xl transition">
                Kelola Kategori
            </a>
            <a href="{{ route('categories.create') }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                + Kategori
            </a>
        </div>
    </div>

    @if($categories->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="text-5xl mb-4">📊</div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada data pengeluaran</h3>
        <p class="text-gray-400 text-sm mb-6">Buat kategori dan tag tagihan untuk mulai melacak pengeluaranmu.</p>
        <a href="{{ route('categories.create') }}"
            class="inline-block px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
            Buat Kategori Pertama
        </a>
    </div>
    @else

    {{-- ── Top stats ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-400 mb-1">Total Pengeluaran</div>
            <div class="text-xl font-bold text-indigo-700">Rp {{ number_format($totalSpentAll, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">dari semua kategori</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-400 mb-1">Total Tagihan</div>
            <div class="text-xl font-bold text-gray-900">{{ $totalBillsAll }}</div>
            <div class="text-xs text-gray-400 mt-1">tagihan tercatat</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-400 mb-1">Kategori Aktif</div>
            <div class="text-xl font-bold text-gray-900">{{ $categoryTotals->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">dari {{ $categories->count() }} kategori</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="text-xs text-gray-400 mb-1">Rata-rata per Tagihan</div>
            <div class="text-xl font-bold text-gray-900">
                Rp {{ $totalBillsAll > 0 ? number_format($totalSpentAll / $totalBillsAll, 0, ',', '.') : '0' }}
            </div>
            <div class="text-xs text-gray-400 mt-1">per transaksi</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- ── Category breakdown ── --}}
        <div class="lg:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Pengeluaran per Kategori</h2>

            @if($categoryTotals->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">Belum ada tagihan yang ditandai dengan kategori.</p>
            @else
            <div class="space-y-3">
                @foreach($categoryTotals as $cat)
                @php
                    $pct = $totalSpentAll > 0 ? round(($cat['total'] / $totalSpentAll) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-base">{{ $cat['icon'] }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $cat['name'] }}</span>
                            <span class="text-xs text-gray-400">({{ $cat['count'] }} tagihan)</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all"
                            style="width: {{ $pct }}%; background-color: {{ $cat['color'] }}">
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5 text-right">
                        Rp {{ number_format($cat['total'], 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── Monthly trend ── --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Tren 6 Bulan Terakhir</h2>

            @php $maxMonthly = $monthly->max('total'); @endphp

            @if($maxMonthly == 0)
            <p class="text-sm text-gray-400 text-center py-8">Belum ada data pengeluaran 6 bulan terakhir.</p>
            @else
            <div class="flex items-end gap-2 h-40 mb-3">
                @foreach($monthly as $month)
                @php
                    $barPct = $maxMonthly > 0 ? ($month['total'] / $maxMonthly) * 100 : 0;
                    $isCurrentMonth = $loop->last;
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="text-xs text-gray-400 font-medium">
                        @if($month['total'] > 0)
                        Rp {{ number_format($month['total'] / 1000, 0, ',', '.') }}K
                        @endif
                    </div>
                    <div class="w-full rounded-t-lg transition-all relative group"
                        style="height: {{ max($barPct, 2) }}%; background-color: {{ $isCurrentMonth ? '#4F46E5' : '#C7D2FE' }}; min-height: 4px;">
                        <!-- Tooltip -->
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                            Rp {{ number_format($month['total'], 0, ',', '.') }}<br>{{ $month['count'] }} tagihan
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-2">
                @foreach($monthly as $month)
                <div class="flex-1 text-center text-xs text-gray-400">{{ $month['label'] }}</div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── Per-category cards ── --}}
    <div class="mb-6">
        <h2 class="font-semibold text-gray-900 mb-4">Detail per Kategori</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
            @php
                $catTotal  = $category->bills->sum(fn($b) => $b->grand_total);
                $catCount  = $category->bills->count();
                $latestBill = $category->bills->sortByDesc('date')->first();
            @endphp
            <a href="{{ route('categories.show', $category) }}"
                class="block bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-indigo-200 hover:shadow-md transition">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl"
                        style="background-color: {{ $category->color }}22">
                        {{ $category->icon }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                        <div class="text-xs text-gray-400">{{ $catCount }} tagihan</div>
                    </div>
                    <div class="ml-auto text-right">
                        <div class="font-bold text-gray-900">Rp {{ number_format($catTotal, 0, ',', '.') }}</div>
                        @if($totalSpentAll > 0)
                        <div class="text-xs text-gray-400">{{ round(($catTotal / $totalSpentAll) * 100, 1) }}% dari total</div>
                        @endif
                    </div>
                </div>

                @if($catTotal > 0)
                <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3">
                    <div class="h-1.5 rounded-full"
                        style="width: {{ $totalSpentAll > 0 ? round(($catTotal / $totalSpentAll) * 100, 1) : 0 }}%; background-color: {{ $category->color }}">
                    </div>
                </div>
                @endif

                @if($latestBill)
                <div class="text-xs text-gray-400">
                    Terakhir: <span class="text-gray-600">{{ $latestBill->name }}</span>
                    · {{ $latestBill->date->format('d M Y') }}
                </div>
                @else
                <div class="text-xs text-gray-400 italic">Belum ada tagihan</div>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    {{-- ── Recent bills across all categories ── --}}
    @if($recentBills->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Tagihan Terbaru (semua kategori)</h2>
        <div class="space-y-0">
            @foreach($recentBills as $bill)
            @php
                $billRoute = $bill->status === 'settled'
                    ? route('bills.show', $bill)
                    : ($bill->status === 'calculated' ? route('bills.split.page', $bill) : route('bills.items.page', $bill));

                // Find which categories this bill belongs to
                $billCats = $categories->filter(fn($c) => $c->bills->contains('id', $bill->id));
            @endphp
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-1">
                        @foreach($billCats->take(3) as $bc)
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm border-2 border-white"
                            style="background-color: {{ $bc->color }}22">
                            {{ $bc->icon }}
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <a href="{{ $billRoute }}"
                            class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                            {{ $bill->name }}
                        </a>
                        <div class="text-xs text-gray-400">
                            {{ $bill->group->name }} · {{ $bill->date->format('d M Y') }}
                        </div>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-4">
                    <div class="font-semibold text-gray-900 text-sm">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $bill->status === 'settled'    ? 'bg-gray-100 text-gray-500'   :
                           ($bill->status === 'calculated' ? 'bg-green-100 text-green-700' :
                                                             'bg-yellow-100 text-yellow-700') }}">
                        {{ ['draft'=>'Draft','calculated'=>'Terhitung','settled'=>'Selesai'][$bill->status] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif
</x-app-layout>
