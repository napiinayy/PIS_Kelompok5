<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1F2937; background: white; }
        .page { padding: 32px; }
        .header { border-bottom: 3px solid #4F46E5; padding-bottom: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .logo { font-size: 22px; font-weight: 700; color: #4F46E5; }
        .logo span { color: #1F2937; }
        .bill-title { font-size: 18px; font-weight: 700; color: #1F2937; margin-bottom: 4px; }
        .bill-meta { color: #6B7280; font-size: 11px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 13px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; padding-bottom: 6px; margin-bottom: 10px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .summary-box { background: #F9FAFB; border-radius: 8px; padding: 10px 12px; border: 1px solid #E5E7EB; }
        .summary-box.highlight { background: #EEF2FF; border-color: #C7D2FE; }
        .summary-label { font-size: 10px; color: #9CA3AF; margin-bottom: 4px; }
        .summary-value { font-size: 14px; font-weight: 700; color: #1F2937; }
        .summary-box.highlight .summary-value { color: #4F46E5; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #4F46E5; color: white; padding: 8px 10px; text-align: left; font-size: 11px; font-weight: 600; }
        td { padding: 8px 10px; border-bottom: 1px solid #F3F4F6; font-size: 11px; }
        tr:nth-child(even) td { background: #F9FAFB; }
        .split-row td { font-size: 12px; }
        .split-total { font-weight: 700; color: #4F46E5; }
        .paid-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 500; }
        .paid { background: #D1FAE5; color: #065F46; }
        .unpaid { background: #FEF3C7; color: #92400E; }
        .footer { margin-top: 32px; border-top: 1px solid #E5E7EB; padding-top: 12px; display: flex; justify-content: space-between; color: #9CA3AF; font-size: 10px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <div class="logo">Split<span>Bill</span></div>
            <div style="color:#6B7280;font-size:10px;margin-top:2px;">Kelompok 5 SI4803 — Telkom University</div>
        </div>
        <div style="text-align:right;">
            <div class="bill-title">{{ $bill->name }}</div>
            <div class="bill-meta">
                {{ $bill->group->name }} &nbsp;·&nbsp;
                {{ $bill->restaurant_name ?? 'N/A' }} &nbsp;·&nbsp;
                {{ $bill->date->format('d F Y') }}
            </div>
            <div class="bill-meta" style="margin-top:2px;">Digenerate: {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <!-- Summary -->
    <div class="summary-grid">
        <div class="summary-box"><div class="summary-label">Subtotal</div><div class="summary-value">Rp {{ number_format($bill->subtotal,0,',','.') }}</div></div>
        <div class="summary-box"><div class="summary-label">Pajak ({{ $bill->tax_percent }}%)</div><div class="summary-value">Rp {{ number_format($bill->tax_amount,0,',','.') }}</div></div>
        <div class="summary-box"><div class="summary-label">Service ({{ $bill->service_percent }}%)</div><div class="summary-value">Rp {{ number_format($bill->service_amount,0,',','.') }}</div></div>
        <div class="summary-box highlight"><div class="summary-label">Grand Total</div><div class="summary-value">Rp {{ number_format($bill->grand_total,0,',','.') }}</div></div>
    </div>

    <!-- Items -->
    <div class="section">
        <div class="section-title">Detail Item Pesanan</div>
        <table>
            <thead><tr><th>#</th><th>Nama Item</th><th>Harga Satuan</th><th>Qty</th><th style="text-align:right;">Subtotal</th></tr></thead>
            <tbody>
                @foreach($bill->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>Rp {{ number_format($item->price,0,',','.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td style="text-align:right;font-weight:600;">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Split results -->
    @if($bill->participants->filter(fn($p)=>$p->splitResult)->isNotEmpty())
    <div class="section">
        <div class="section-title">Pembagian Tagihan per Peserta</div>
        <table>
            <thead><tr><th>Nama</th><th>Subtotal Pesanan</th><th>Bagian Pajak</th><th>Bagian Service</th><th>TOTAL BAYAR</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($bill->participants as $participant)
                @if($participant->splitResult)
                @php $r = $participant->splitResult; @endphp
                <tr class="split-row">
                    <td><strong>{{ $participant->name }}</strong></td>
                    <td>Rp {{ number_format($r->subtotal,0,',','.') }}</td>
                    <td>Rp {{ number_format($r->tax_share,0,',','.') }}</td>
                    <td>Rp {{ number_format($r->service_share,0,',','.') }}</td>
                    <td class="split-total">Rp {{ number_format($r->total,0,',','.') }}</td>
                    <td><span class="paid-badge {{ $participant->is_paid ? 'paid' : 'unpaid' }}">{{ $participant->is_paid ? 'Lunas' : 'Belum' }}</span></td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <span>Split Bill — Kelompok 5 SI4803 — Telkom University 2026</span>
        <span>Generated by SplitBill App</span>
    </div>
</div>
</body></html>
