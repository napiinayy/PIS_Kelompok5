<x-app-layout>
    <x-slot name="title">Tambah Item — {{ $bill->name }}</x-slot>

    <!-- Progress bar -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('groups.show', $bill->group) }}" class="text-indigo-600 hover:underline">{{ $bill->group->name }}</a>
            <span class="text-gray-400">›</span>
            <span class="text-gray-400">{{ $bill->name }}</span>
        </div>
        <div class="flex items-center gap-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-bold">✓</div>
                <span class="text-sm font-medium text-green-600 hidden sm:block">Buat Tagihan</span>
            </div>
            <div class="flex-1 h-1 bg-indigo-500 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold">2</div>
                <span class="text-sm font-semibold text-indigo-700 hidden sm:block">Tambah Item</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-sm font-bold">3</div>
                <span class="text-sm text-gray-400 hidden sm:block">Bagi Tagihan</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- LEFT: OCR Upload -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-1 flex items-center gap-2">
                    <span>📷</span> Scan Struk (Otomatis)
                </h2>
                <p class="text-xs text-gray-400 mb-4">Upload foto struk, sistem akan baca item dan harga secara otomatis</p>

                <!-- Drop zone -->
                <div id="drop-zone"
                    class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-indigo-400 hover:bg-indigo-50 transition cursor-pointer"
                    onclick="document.getElementById('scan-input').click()"
                    ondragover="event.preventDefault(); this.classList.add('border-indigo-400','bg-indigo-50')"
                    ondragleave="this.classList.remove('border-indigo-400','bg-indigo-50')"
                    ondrop="handleDrop(event)">
                    <input type="file" id="scan-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="uploadScan(this.files[0])">
                    <div id="drop-icon">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-500">Klik atau drag foto struk ke sini</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — maks 5MB</p>
                    </div>
                    <div id="upload-progress" class="hidden">
                        <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mx-auto mb-3"></div>
                        <p class="text-sm text-indigo-600 font-medium" id="progress-text">Mengupload...</p>
                    </div>
                </div>

                <!-- OCR scan results -->
                <div id="scan-results" class="mt-4 space-y-3"></div>

                <!-- Existing scans -->
                @foreach($bill->scans as $scan)
                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-100" id="scan-card-{{ $scan->id }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span id="scan-status-{{ $scan->id }}" class="text-sm">
                                @if($scan->status === 'done')
                                    <span class="text-green-600 font-medium">✓ Scan selesai — {{ count($scan->raw_ocr_result['parsed_items'] ?? []) }} item terdeteksi</span>
                                @elseif($scan->status === 'failed')
                                    <span class="text-red-500">✗ Gagal: {{ $scan->error_message }}</span>
                                @else
                                    <span class="text-yellow-600 animate-pulse">⏳ Memproses OCR...</span>
                                @endif
                            </span>
                        </div>
                        <form method="POST" action="{{ route('scans.destroy', [$bill, $scan]) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                        </form>
                    </div>

                    @if($scan->status === 'done' && !empty($scan->raw_ocr_result['parsed_items']))
                    @if(isset($scan->raw_ocr_result['mode']) && $scan->raw_ocr_result['mode'] === 'mock')
                    <div class="text-xs bg-yellow-50 text-yellow-700 px-3 py-2 rounded-lg mb-3 border border-yellow-100">
                        ⚠️ Mode demo — tidak ada Google Vision API key. Item di bawah adalah contoh.
                    </div>
                    @endif
                    <form method="POST" action="{{ route('scans.confirm', [$bill, $scan]) }}">
                        @csrf
                        <div class="space-y-2 mb-3">
                            <div class="grid grid-cols-12 gap-1 text-xs text-gray-400 px-1 mb-1">
                                <div class="col-span-5">Nama Item</div>
                                <div class="col-span-4">Harga</div>
                                <div class="col-span-2">Qty</div>
                                <div class="col-span-1"></div>
                            </div>
                            @foreach($scan->raw_ocr_result['parsed_items'] as $i => $item)
                            <div class="grid grid-cols-12 gap-1 items-center" id="ocr-row-{{ $scan->id }}-{{ $i }}">
                                <input type="text"   name="items[{{ $i }}][name]"     value="{{ $item['name'] }}"          class="col-span-5 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                <input type="number" name="items[{{ $i }}][price]"    value="{{ $item['price'] }}"         class="col-span-4 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                <input type="number" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="col-span-2 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                <button type="button" onclick="removeOcrRow('ocr-row-{{ $scan->id }}-{{ $i }}')" class="col-span-1 text-gray-300 hover:text-red-400 text-lg leading-none">×</button>
                            </div>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                            ✓ Konfirmasi & Tambah {{ count($scan->raw_ocr_result['parsed_items']) }} Item ke Tagihan
                        </button>
                    </form>
                    @endif

                    @if(in_array($scan->status, ['pending', 'processing']))
                    <script>document.addEventListener('DOMContentLoaded', () => startPolling({{ $bill->id }}, {{ $scan->id }}))</script>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT: Manual add + item list -->
        <div class="space-y-6">

            <!-- Manual add -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-1">✏️ Tambah Manual</h2>
                <p class="text-xs text-gray-400 mb-4">Tambahkan item satu per satu</p>
                <form method="POST" action="{{ route('bills.items.store', $bill) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nama Item</label>
                        <input type="text" name="name" placeholder="Contoh: Nasi Goreng Spesial" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Harga (Rp)</label>
                            <input type="number" name="price" placeholder="25000" min="0" step="500" required
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jumlah</label>
                            <input type="number" name="quantity" value="1" min="1"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                        + Tambah Item
                    </button>
                </form>
            </div>

            <!-- Current items list -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Item Saat Ini</h2>
                    <span class="text-xs text-gray-400">{{ $bill->items->count() }} item</span>
                </div>

                @forelse($bill->items as $item)
                <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                        <div class="text-xs text-gray-400">{{ $item->quantity }}× @ Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-sm font-semibold text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                    <form method="POST" action="{{ route('bills.items.destroy', [$bill, $item]) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-300 hover:text-red-400 transition p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">Belum ada item. Upload struk atau tambah manual.</p>
                @endforelse

                @if($bill->items->count() > 0)
                <div class="pt-3 mt-2 border-t border-gray-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($bill->tax_percent > 0)
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-500">Pajak ({{ $bill->tax_percent }}%)</span>
                        <span class="font-medium">Rp {{ number_format($bill->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($bill->service_percent > 0)
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-500">Service ({{ $bill->service_percent }}%)</span>
                        <span class="font-medium">Rp {{ number_format($bill->service_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                        <span class="font-semibold text-gray-900">Grand Total</span>
                        <span class="font-bold text-indigo-700 text-lg">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Participants -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-4">👥 Peserta ({{ $bill->participants->count() }})</h2>
                @foreach($bill->participants as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-700">{{ strtoupper(substr($p->name,0,1)) }}</div>
                        <span class="text-sm text-gray-800">{{ $p->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('bills.participants.destroy', [$bill, $p]) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-gray-300 hover:text-red-400 p-1 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </form>
                </div>
                @endforeach
                <form method="POST" action="{{ route('bills.participants.store', $bill) }}" class="flex gap-2 mt-3">
                    @csrf
                    <input type="text" name="name" placeholder="Tambah peserta lain" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm rounded-xl font-medium">+</button>
                </form>
            </div>

            <!-- Next step button -->
            @if($bill->items->count() > 0)
            <a href="{{ route('bills.split.page', $bill) }}"
                class="flex items-center justify-center gap-2 w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-2xl transition text-sm shadow-md shadow-indigo-200">
                Lanjut ke Pembagian Tagihan →
            </a>
            @else
            <div class="w-full py-3.5 bg-gray-100 text-gray-400 font-semibold rounded-2xl text-sm text-center cursor-not-allowed">
                Tambah item dulu untuk melanjutkan
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
// ── OCR Upload ────────────────────────────────────────────────────────────────
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('border-indigo-400','bg-indigo-50');
    const file = e.dataTransfer.files[0];
    if (file) uploadScan(file);
}

async function uploadScan(file) {
    if (!file) return;

    // Show spinner
    document.getElementById('drop-icon').classList.add('hidden');
    document.getElementById('upload-progress').classList.remove('hidden');
    document.getElementById('progress-text').textContent = 'Mengupload...';

    const formData = new FormData();
    formData.append('image', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res  = await fetch('{{ route('scans.store', $bill) }}', { method: 'POST', body: formData });
        const data = await res.json();

        if (!res.ok) throw new Error(data.message || 'Upload gagal');

        document.getElementById('progress-text').textContent = 'Memproses OCR...';
        startPolling({{ $bill->id }}, data.scan_id);
    } catch (err) {
        document.getElementById('drop-icon').classList.remove('hidden');
        document.getElementById('upload-progress').classList.add('hidden');
        alert('Upload gagal: ' + err.message);
    }
}

// ── OCR Polling ───────────────────────────────────────────────────────────────
function startPolling(billId, scanId) {
    const interval = setInterval(async () => {
        try {
            const res  = await fetch(`/bills/${billId}/scan/${scanId}/status`);
            const data = await res.json();

            if (data.status === 'done' || data.status === 'failed') {
                clearInterval(interval);
                // Reload to show the confirm form cleanly
                location.reload();
            }
        } catch (e) {
            clearInterval(interval);
            location.reload();
        }
    }, 2500);
}

function removeOcrRow(id) {
    document.getElementById(id)?.remove();
}
</script>
