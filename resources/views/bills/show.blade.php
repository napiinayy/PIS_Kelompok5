<x-app-layout>
    <x-slot name="title">{{ $bill->name }}</x-slot>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('groups.show', $bill->group) }}" class="text-sm text-indigo-600 hover:underline">← {{ $bill->group->name }}</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $bill->name }}</h1>
            <p class="text-gray-500 text-sm mt-0.5">
                {{ $bill->restaurant_name ?? '' }}{{ $bill->restaurant_name ? ' · ' : '' }}{{ $bill->date->format('d F Y') }}
            </p>
            @if($bill->status === 'settled')
            <span class="inline-block mt-2 px-3 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">✓ Tagihan ditutup</span>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @if($bill->status !== 'settled')
            <a href="{{ route('bills.split.page', $bill) }}" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium">Lihat Pembagian</a>
            @endif
            <a href="{{ route('export.pdf', $bill) }}" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium">📄 Export PDF</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">

            <!-- Summary -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div><div class="text-xs text-gray-400 mb-1">Subtotal</div><div class="font-semibold text-gray-900">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</div></div>
                    <div><div class="text-xs text-gray-400 mb-1">Pajak ({{ $bill->tax_percent }}%)</div><div class="font-semibold text-gray-900">Rp {{ number_format($bill->tax_amount, 0, ',', '.') }}</div></div>
                    <div><div class="text-xs text-gray-400 mb-1">Service ({{ $bill->service_percent }}%)</div><div class="font-semibold text-gray-900">Rp {{ number_format($bill->service_amount, 0, ',', '.') }}</div></div>
                    <div class="bg-indigo-50 rounded-xl p-2"><div class="text-xs text-indigo-500 mb-1">Grand Total</div><div class="text-lg font-bold text-indigo-700">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</div></div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Item Pesanan</h3>
                @foreach($bill->items as $item)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                        <div class="text-xs text-gray-400">{{ $item->quantity }}× @ Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                    </div>
                    <div class="font-semibold text-gray-900 text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>

            <!-- Split results (read-only) -->
            @if($bill->participants->filter(fn($p) => $p->splitResult)->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Hasil Pembagian</h3>
                @foreach($bill->participants as $participant)
                @if($participant->splitResult)
                @php $r = $participant->splitResult; @endphp
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-700">{{ strtoupper(substr($participant->name,0,1)) }}</div>
                        <span class="text-sm font-medium text-gray-900">{{ $participant->name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-indigo-700">Rp {{ number_format($r->total, 0, ',', '.') }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $participant->is_paid ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $participant->is_paid ? '✓ Lunas' : 'Belum' }}
                        </span>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>

        <!-- Right sidebar -->
        <div class="space-y-6">

            <!-- Categories (Fitur 3) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">🏷️ Kategori</h3>

                @php $userCategories = auth()->user()->categories ?? \App\Models\Category::where('user_id', auth()->id())->get(); @endphp

                @if($bill->categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($bill->categories as $cat)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"
                        style="background-color: {{ $cat->color }}22; color: {{ $cat->color }};">
                        <span>{{ $cat->icon }}</span>
                        <span>{{ $cat->name }}</span>
                        <form method="POST" action="{{ route('categories.bills.detach', [$cat, $bill]) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="ml-1 opacity-60 hover:opacity-100 font-bold leading-none">×</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif

                @php $attachedIds = $bill->categories->pluck('id')->toArray(); @endphp
                @php $available = \App\Models\Category::where('user_id', auth()->id())->whereNotIn('id', $attachedIds)->get(); @endphp

                @if($available->isNotEmpty())
                <form method="POST" action="{{ route('categories.bills.attach', $available->first()) }}" id="cat-form">
                    @csrf
                    <input type="hidden" name="bill_id" value="{{ $bill->id }}">
                    <select name="_category_id" onchange="submitCatForm(this)"
                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        <option value="">+ Tambah kategori...</option>
                        @foreach($available as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </form>
                @elseif($bill->categories->isEmpty())
                <p class="text-xs text-gray-400">
                    <a href="{{ route('categories.create') }}" class="text-indigo-500 hover:underline">Buat kategori</a> untuk mengelompokkan tagihan ini.
                </p>
                @endif

                <a href="{{ route('categories.index') }}" class="block text-xs text-indigo-500 hover:underline mt-3">Kelola semua kategori →</a>
            </div>

            <!-- Share link -->
            @if($bill->sharedLinks->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-3">🔗 Link Berbagi</h3>
                @php $link = $bill->sharedLinks->first(); @endphp
                <div class="flex gap-2">
                    <input type="text" value="{{ route('share.view', $link->token) }}" readonly
                        onclick="this.select(); document.execCommand('copy');"
                        class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-lg px-2 py-2 cursor-pointer"
                        title="Klik untuk copy">
                    <a href="{{ 'https://wa.me/?text=' . urlencode("🧾 {$bill->name}\n" . route('share.view', $link->token)) }}"
                        target="_blank"
                        class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-xs rounded-lg font-medium">WA</a>
                </div>
            </div>
            @endif

            <!-- Save as template (Fitur 4) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-2">📋 Simpan sebagai Template</h3>
                <p class="text-xs text-gray-400 mb-4">Gunakan tagihan ini sebagai template untuk membuat tagihan serupa di masa depan.</p>
                <form method="POST" action="{{ route('bills.save-as-template') }}">
                    @csrf
                    <input type="hidden" name="bill_id" value="{{ $bill->id }}">
                    <button type="submit"
                        class="w-full py-2.5 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 text-sm font-medium rounded-xl transition">
                        Simpan sebagai Template
                    </button>
                </form>
                <a href="{{ route('templates.index') }}" class="block text-xs text-indigo-500 hover:underline mt-3">Lihat semua template →</a>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function submitCatForm(select) {
    if (!select.value) return;
    const form = document.getElementById('cat-form');
    form.action = form.action.replace(/\/categories\/\d+\/bills/, `/categories/${select.value}/bills`);
    form.submit();
}
</script>
