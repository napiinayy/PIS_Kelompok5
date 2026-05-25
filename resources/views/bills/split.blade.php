<x-app-layout>
    <x-slot name="title">Bagi Tagihan — {{ $bill->name }}</x-slot>

    <!-- Progress -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('groups.show', $bill->group) }}" class="text-indigo-600 hover:underline">{{ $bill->group->name }}</a>
            <span class="text-gray-400">›</span>
            <a href="{{ route('bills.items.page', $bill) }}" class="text-indigo-600 hover:underline">{{ $bill->name }}</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-500">Pembagian</span>
        </div>
        <div class="flex items-center gap-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-bold">✓</div>
                <span class="text-sm font-medium text-green-600 hidden sm:block">Buat Tagihan</span>
            </div>
            <div class="flex-1 h-1 bg-green-400 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-bold">✓</div>
                <span class="text-sm font-medium text-green-600 hidden sm:block">Tambah Item</span>
            </div>
            <div class="flex-1 h-1 bg-indigo-500 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold">3</div>
                <span class="text-sm font-semibold text-indigo-700 hidden sm:block">Bagi Tagihan</span>
            </div>
        </div>
    </div>

    @php
        $allPaid     = $bill->participants->filter(fn($p) => $p->splitResult)->every(fn($p) => $p->is_paid);
        $anyResult   = $bill->participants->filter(fn($p) => $p->splitResult)->isNotEmpty();
        $paidCount   = $bill->participants->filter(fn($p) => $p->is_paid)->count();
        $totalCount  = $bill->participants->count();
    @endphp

    <!-- All paid banner -->
    @if($allPaid && $anyResult)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="text-3xl">🎉</div>
            <div>
                <div class="font-semibold text-green-800">Semua peserta sudah lunas!</div>
                <div class="text-sm text-green-600">Kamu bisa menutup tagihan ini sekarang.</div>
            </div>
        </div>
        <form method="POST" action="{{ route('bills.settle', $bill) }}"
            onsubmit="return confirm('Tutup tagihan \'{{ $bill->name }}\'? Tagihan tidak bisa diedit lagi setelah ditutup.')">
            @csrf
            <button type="submit"
                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition shadow-sm whitespace-nowrap">
                ✓ Tutup Tagihan
            </button>
        </form>
    </div>
    @elseif($anyResult)
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-center gap-3">
        <div class="text-xl">💳</div>
        <div class="text-sm text-blue-700">
            <strong>{{ $paidCount }} dari {{ $totalCount }}</strong> peserta sudah lunas.
            Tandai semua sebagai lunas untuk menutup tagihan.
        </div>
    </div>
    @endif

    <!-- Summary bar -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-6 items-center justify-between">
        <div class="flex gap-6 flex-wrap">
            <div><div class="text-xs text-gray-400">Subtotal</div><div class="font-semibold text-gray-900">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</div></div>
            @if($bill->tax_percent > 0)
            <div><div class="text-xs text-gray-400">Pajak ({{ $bill->tax_percent }}%)</div><div class="font-semibold text-gray-900">Rp {{ number_format($bill->tax_amount, 0, ',', '.') }}</div></div>
            @endif
            @if($bill->service_percent > 0)
            <div><div class="text-xs text-gray-400">Service ({{ $bill->service_percent }}%)</div><div class="font-semibold text-gray-900">Rp {{ number_format($bill->service_amount, 0, ',', '.') }}</div></div>
            @endif
            <div class="bg-indigo-50 rounded-xl px-4 py-1">
                <div class="text-xs text-indigo-400">Grand Total</div>
                <div class="font-bold text-indigo-700 text-lg">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</div>
            </div>
        </div>
        <a href="{{ route('bills.items.page', $bill) }}" class="text-sm text-indigo-600 hover:underline">← Edit Item</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- LEFT: Assign -->
        <div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-1">🧾 Siapa Pesan Apa?</h2>
                <p class="text-xs text-gray-400 mb-5">Centang nama orang yang memesan setiap item. Satu item bisa diceklis oleh beberapa orang — biayanya akan dibagi rata di antara mereka.</p>

                <form method="POST" action="{{ route('bills.assign', $bill) }}">
                    @csrf
                    <div class="space-y-4">
                        @foreach($bill->items as $item)
                        @php $assigned = $item->assignments->pluck('participant_id')->toArray(); @endphp
                        <div class="border border-gray-100 rounded-xl p-4 hover:border-indigo-100 transition">
                            <div class="mb-3">
                                <div class="font-medium text-sm text-gray-900">{{ $item->name }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $item->quantity }}× @ Rp {{ number_format($item->price, 0, ',', '.') }}
                                    = <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($bill->participants as $participant)
                                <label class="flex items-center gap-2 cursor-pointer group select-none">
                                    <input type="checkbox"
                                        name="assignments[{{ $item->id }}][]"
                                        value="{{ $participant->id }}"
                                        {{ in_array($participant->id, $assigned) ? 'checked' : '' }}
                                        class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                    <span class="text-sm text-gray-700 group-hover:text-indigo-700">{{ $participant->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full mt-5 py-2.5 border border-indigo-300 text-indigo-600 hover:bg-indigo-50 font-medium rounded-xl text-sm transition">
                        💾 Simpan Assignment
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Calculate + Results + Share + Close -->
        <div class="space-y-6">

            <!-- Calculate -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-1">⚡ Hitung Pembagian</h2>
                <p class="text-xs text-gray-400 mb-4">Pilih metode lalu klik hitung. Bisa diulang kapan saja.</p>
                <form method="POST" action="{{ route('bills.calculate', $bill) }}">
                    @csrf
                    <div class="space-y-3 mb-4">
                        <label class="flex items-start gap-3 p-4 border-2 border-indigo-200 rounded-xl cursor-pointer bg-indigo-50">
                            <input type="radio" name="method" value="proportional" checked class="mt-0.5 text-indigo-600">
                            <div>
                                <div class="font-medium text-sm text-gray-900">Proporsional</div>
                                <div class="text-xs text-gray-500 mt-0.5">Bayar sesuai item yang dipesan. Tax & service dibagi proporsional.</div>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-gray-200">
                            <input type="radio" name="method" value="equal" class="mt-0.5 text-indigo-600">
                            <div>
                                <div class="font-medium text-sm text-gray-900">Merata</div>
                                <div class="text-xs text-gray-500 mt-0.5">Grand total dibagi sama rata. Tidak perlu assign item.</div>
                            </div>
                        </label>
                    </div>
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                        Hitung Sekarang
                    </button>
                </form>
            </div>

            <!-- Results -->
            @if($anyResult)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-4">✅ Hasil Pembagian</h2>
                @foreach($bill->participants as $participant)
                @if($participant->splitResult)
                @php $r = $participant->splitResult; @endphp
                <div class="py-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-700">
                                {{ strtoupper(substr($participant->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-sm text-gray-900">{{ $participant->name }}</div>
                                <div class="text-xs text-gray-400">
                                    item: {{ number_format($r->subtotal,0,',','.') }}
                                    @if($r->tax_share > 0) + tax: {{ number_format($r->tax_share,0,',','.') }} @endif
                                    @if($r->service_share > 0) + svc: {{ number_format($r->service_share,0,',','.') }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="font-bold text-indigo-700 text-base">
                                Rp {{ number_format($r->total, 0, ',', '.') }}
                            </div>
                            <form method="POST" action="{{ route('share.paid', [$bill, $participant]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs px-2.5 py-1 rounded-full border transition
                                    {{ $participant->is_paid
                                        ? 'bg-green-100 border-green-200 text-green-700'
                                        : 'bg-gray-50 border-gray-200 text-gray-500 hover:border-green-300 hover:text-green-600' }}">
                                    {{ $participant->is_paid ? '✓ Lunas' : 'Belum' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach

                <!-- Close bill button (shown inside results when all paid) -->
                @if($allPaid)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('bills.settle', $bill) }}"
                        onsubmit="return confirm('Tutup tagihan ini? Tidak bisa diedit lagi setelah ditutup.')">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
                            ✓ Tutup Tagihan & Kembali ke Grup
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @endif

            <!-- Share & Export -->
            @if($anyResult)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-4">🔗 Bagikan & Export</h2>
                @if($bill->sharedLinks->isNotEmpty())
                @php $link = $bill->sharedLinks->first(); @endphp
                <div class="bg-gray-50 rounded-xl p-3 mb-3">
                    <p class="text-xs text-gray-500 mb-1.5">Link aktif — klik untuk copy:</p>
                    <div class="flex gap-2">
                        <input type="text" value="{{ route('share.view', $link->token) }}" readonly
                            onclick="this.select(); document.execCommand('copy'); this.style.borderColor='#4F46E5'; setTimeout(()=>this.style.borderColor='',1500);"
                            class="flex-1 text-xs bg-white border border-gray-200 rounded-lg px-2 py-2 cursor-pointer"
                            title="Klik untuk copy">
                        <a href="{{ 'https://wa.me/?text=' . urlencode("🧾 *{$bill->name}*\nCek tagihan kita: " . route('share.view', $link->token)) }}"
                            target="_blank"
                            class="flex items-center gap-1 px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-xs rounded-lg font-medium whitespace-nowrap">
                            📲 WA
                        </a>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('export.pdf', $bill) }}"
                        class="flex-1 text-center py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-xl border border-red-100 transition">
                        📄 Download PDF
                    </a>
                    <form method="POST" action="{{ route('share.deactivate', $bill) }}" class="inline">
                        @csrf
                        <button class="px-3 py-2 text-xs text-gray-400 hover:text-red-500 border border-gray-200 rounded-xl">
                            Nonaktifkan
                        </button>
                    </form>
                </div>
                @else
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('share.generate', $bill) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                            🔗 Generate Link
                        </button>
                    </form>
                    <a href="{{ route('export.pdf', $bill) }}"
                        class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-xl border border-red-100 transition whitespace-nowrap">
                        📄 PDF
                    </a>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
