<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>{{ $bill->name }} — Split Bill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet"/>
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-white py-10 px-4">
<div class="max-w-md mx-auto">
    <!-- Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-600 rounded-xl mb-3">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900">{{ $bill->name }}</h1>
        <p class="text-gray-500 text-sm">{{ $bill->group->name }} · {{ $bill->date->format('d F Y') }}</p>
        @if($bill->restaurant_name)<p class="text-gray-400 text-xs mt-1">{{ $bill->restaurant_name }}</p>@endif
    </div>

    <!-- Summary -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="grid grid-cols-3 gap-3 text-center text-sm">
            <div><div class="text-gray-400 text-xs mb-1">Subtotal</div><div class="font-semibold">Rp {{ number_format($bill->subtotal,0,',','.') }}</div></div>
            <div><div class="text-gray-400 text-xs mb-1">Pajak+Service</div><div class="font-semibold">Rp {{ number_format($bill->tax_amount + $bill->service_amount,0,',','.') }}</div></div>
            <div class="bg-indigo-50 rounded-xl p-2"><div class="text-indigo-500 text-xs mb-1">Total</div><div class="font-bold text-indigo-700">Rp {{ number_format($bill->grand_total,0,',','.') }}</div></div>
        </div>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="font-semibold text-gray-900 mb-3 text-sm">Item Pesanan</h3>
        @foreach($bill->items as $item)
        <div class="flex justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
            <span class="text-gray-700">{{ $item->name }} @if($item->quantity>1)<span class="text-gray-400">×{{ $item->quantity }}</span>@endif</span>
            <span class="font-medium text-gray-900">Rp {{ number_format($item->subtotal,0,',','.') }}</span>
        </div>
        @endforeach
    </div>

    <!-- Split results -->
    @if($bill->participants->filter(fn($p)=>$p->splitResult)->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="font-semibold text-gray-900 mb-3 text-sm">Pembagian Tagihan</h3>
        @foreach($bill->participants as $participant)
        @if($participant->splitResult)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-700">{{ strtoupper(substr($participant->name,0,1)) }}</div>
                <div>
                    <div class="font-medium text-sm text-gray-900">{{ $participant->name }}</div>
                    <div class="text-xs text-gray-400">Items: Rp {{ number_format($participant->splitResult->subtotal,0,',','.') }}</div>
                </div>
            </div>
            <div class="text-right">
                <div class="font-bold text-indigo-700">Rp {{ number_format($participant->splitResult->total,0,',','.') }}</div>
                <span class="text-xs {{ $participant->is_paid ? 'text-green-600' : 'text-orange-500' }}">{{ $participant->is_paid ? '✓ Lunas' : 'Belum bayar' }}</span>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    <!-- WhatsApp share button -->
    @php
        $msg = "🧾 *{$bill->name}*\n";
        $msg .= "📍 {$bill->restaurant_name}\n";
        $msg .= "💰 Total: Rp " . number_format($bill->grand_total,0,',','.') . "\n\n";
        foreach($bill->participants as $p) {
            if($p->splitResult) $msg .= "• {$p->name}: Rp " . number_format($p->splitResult->total,0,',','.') . "\n";
        }
        $msg .= "\nCek detail: " . request()->url();
    @endphp
    <a href="https://wa.me/?text={{ urlencode($msg) }}" target="_blank"
        class="flex items-center justify-center gap-2 w-full py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-2xl transition text-sm">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Bagikan via WhatsApp
    </a>

    <p class="text-center text-xs text-gray-400 mt-6">Split Bill · Kelompok 5 SI4803 · Telkom University</p>
</div>
</body></html>
