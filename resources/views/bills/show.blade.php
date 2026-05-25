<x-app-layout>
    <x-slot name="title">{{ $bill->name }}</x-slot>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('groups.show', $bill->group) }}" class="text-sm text-indigo-600 hover:underline">← {{ $bill->group->name }}</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $bill->name }}</h1>
            <p class="text-gray-500 text-sm">{{ $bill->restaurant_name ?? '' }} · {{ $bill->date->format('d F Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('bills.edit', $bill) }}" class="px-4 py-2 text-sm border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">Edit</a>
            <a href="{{ route('export.pdf', $bill) }}" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium">Export PDF</a>
            @if($bill->splitResults->isEmpty())
            <form method="POST" action="{{ route('bills.calculate', $bill) }}" class="inline flex gap-2">
                @csrf
                <select name="method" class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="proportional">Proporsional</option>
                    <option value="equal">Merata</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium">Hitung Split</button>
            </form>
            @else
            <form method="POST" action="{{ route('bills.calculate', $bill) }}" class="inline flex gap-2">
                @csrf
                <select name="method" class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="proportional">Proporsional</option>
                    <option value="equal">Merata</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-medium">Hitung Ulang</button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Left column -->
        <div class="xl:col-span-2 space-y-6">

            <!-- Summary card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Subtotal</div>
                        <div class="font-semibold text-gray-900">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Pajak ({{ $bill->tax_percent }}%)</div>
                        <div class="font-semibold text-gray-900">Rp {{ number_format($bill->tax_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">Service ({{ $bill->service_percent }}%)</div>
                        <div class="font-semibold text-gray-900">Rp {{ number_format($bill->service_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-2">
                        <div class="text-xs text-indigo-500 mb-1">Total</div>
                        <div class="text-lg font-bold text-indigo-700">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- OCR Scanner -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span>📷</span> Scan Struk (OCR)
                </h3>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-indigo-300 transition cursor-pointer" onclick="document.getElementById('scan-input').click()">
                    <input type="file" id="scan-input" accept="image/*" class="hidden" onchange="uploadScan(this)">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Klik untuk upload foto struk</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — maks 5MB</p>
                </div>
                <div id="upload-status" class="text-sm text-gray-500 mt-2 hidden"></div>

                @foreach($bill->scans as $scan)
                <div class="mt-4 p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span id="scan-status-{{ $scan->id }}" class="text-sm">
                            @if($scan->status === 'done')
                                <span class="text-green-600 font-medium">✓ Selesai</span>
                            @elseif($scan->status === 'failed')
                                <span class="text-red-600">✗ Gagal: {{ $scan->error_message }}</span>
                            @else
                                <span class="text-yellow-600 animate-pulse">⏳ Memproses...</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('scans.destroy', [$bill, $scan]) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                    </div>
                    @if($scan->status === 'done' && !empty($scan->raw_ocr_result['parsed_items']))
                    <form method="POST" action="{{ route('scans.confirm', [$bill, $scan]) }}">
                        @csrf
                        <div id="ocr-items-{{ $scan->id }}" class="space-y-2 mb-3">
                            @foreach($scan->raw_ocr_result['parsed_items'] as $i => $item)
                            <div class="flex gap-2 items-center py-1 border-b border-gray-100">
                                <input type="text"   name="items[{{ $i }}][name]"     value="{{ $item['name'] }}"            class="flex-1 text-sm border border-gray-200 rounded-lg px-2 py-1.5">
                                <input type="number" name="items[{{ $i }}][price]"    value="{{ $item['price'] }}"           class="w-28 text-sm border border-gray-200 rounded-lg px-2 py-1.5">
                                <input type="number" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}"   class="w-16 text-sm border border-gray-200 rounded-lg px-2 py-1.5">
                            </div>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition">
                            Konfirmasi & Tambah ke Tagihan
                        </button>
                    </form>
                    @endif
                    @if(in_array($scan->status, ['pending', 'processing']))
                    <script>document.addEventListener('DOMContentLoaded', () => pollScanStatus({{ $bill->id }}, {{ $scan->id }}))</script>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Items list -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Item Tagihan</h3>
                <div class="space-y-2 mb-4">
                    @forelse($bill->items as $item)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                            <div class="text-xs text-gray-400">{{ $item->quantity }}x @ Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        <form method="POST" action="{{ route('bills.items.destroy', [$bill, $item]) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-gray-300 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada item. Upload struk atau tambah manual.</p>
                    @endforelse
                </div>
                <!-- Add item form -->
                <form method="POST" action="{{ route('bills.items.store', $bill) }}" class="flex gap-2 flex-wrap">
                    @csrf
                    <input type="text"   name="name"     placeholder="Nama item" required class="flex-1 min-w-0 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    <input type="number" name="price"    placeholder="Harga" required min="0" step="500" class="w-28 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    <input type="number" name="quantity" value="1" min="1" class="w-16 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">+ Tambah</button>
                </form>
            </div>
        </div>

        <!-- Right column -->
        <div class="space-y-6">

            <!-- Split results -->
            @if($bill->splitResults->isNotEmpty())
            <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">✅ Hasil Pembagian</h3>
                @foreach($bill->participants as $participant)
                @php $result = $participant->splitResult; @endphp
                @if($result)
                <div class="py-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-sm text-gray-900">{{ $participant->name }}</span>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-indigo-700">Rp {{ number_format($result->total, 0, ',', '.') }}</span>
                            <form method="POST" action="{{ route('share.paid', [$bill, $participant]) }}" class="inline">
                                @csrf
                                <button class="text-xs px-2 py-0.5 rounded-full {{ $participant->is_paid ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $participant->is_paid ? '✓ Lunas' : 'Belum' }}
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        Items: Rp {{ number_format($result->subtotal, 0, ',', '.') }} +
                        Tax: Rp {{ number_format($result->tax_share, 0, ',', '.') }} +
                        Service: Rp {{ number_format($result->service_share, 0, ',', '.') }}
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            <!-- Participants -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Peserta</h3>
                @foreach($bill->participants as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-700">
                            {{ strtoupper(substr($p->name, 0, 1)) }}
                        </div>
                        <span class="text-sm text-gray-900">{{ $p->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('bills.participants.destroy', [$bill, $p]) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-gray-300 hover:text-red-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
                @endforeach
                <form method="POST" action="{{ route('bills.participants.store', $bill) }}" class="flex gap-2 mt-3">
                    @csrf
                    <input type="text" name="name" placeholder="Nama peserta" required class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-xl">+</button>
                </form>
            </div>

            <!-- Share -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">🔗 Bagikan</h3>
                @if($bill->sharedLinks->isNotEmpty())
                @php $link = $bill->sharedLinks->first(); @endphp
                <div class="bg-gray-50 rounded-xl p-3 mb-3">
                    <p class="text-xs text-gray-500 mb-1">Link aktif:</p>
                    <div class="flex gap-2 items-center">
                        <input type="text" value="{{ route('share.view', $link->token) }}" readonly
                            class="flex-1 text-xs bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-gray-700"
                            onclick="this.select()">
                        <a href="{{ 'https://wa.me/?text=' . urlencode('Cek tagihan kita: ' . route('share.view', $link->token)) }}"
                            target="_blank"
                            class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs rounded-lg font-medium">WA</a>
                    </div>
                </div>
                <form method="POST" action="{{ route('share.deactivate', $bill) }}" class="inline">
                    @csrf
                    <button class="text-xs text-red-500 hover:underline">Nonaktifkan link</button>
                </form>
                @else
                <form method="POST" action="{{ route('share.generate', $bill) }}">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                        Generate Link
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
async function uploadScan(input) {
    if (!input.files[0]) return;
    const status = document.getElementById('upload-status');
    status.classList.remove('hidden');
    status.textContent = 'Mengupload...';

    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res = await fetch('{{ route('scans.store', $bill) }}', { method: 'POST', body: formData });
        const data = await res.json();
        status.textContent = 'Upload berhasil! Memproses OCR...';
        setTimeout(() => location.reload(), 1500);
    } catch (e) {
        status.textContent = 'Upload gagal. Coba lagi.';
    }
}
</script>
