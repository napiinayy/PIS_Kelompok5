<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReceiptOcr;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\ReceiptScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function store(Request $request, Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $request->validate(['image' => 'required|image|max:5120|mimes:jpg,jpeg,png,webp']);

        $path = $request->file('image')->store('scans', 'public');

        $scan = $bill->scans()->create([
            'image_path' => $path,
            'status'     => 'pending',
        ]);

        ProcessReceiptOcr::dispatch($scan);

        return response()->json(['scan_id' => $scan->id, 'status' => 'pending']);
    }

    public function status(Bill $bill, ReceiptScan $scan)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        return response()->json([
            'status' => $scan->status,
            'items'  => $scan->status === 'done'
                ? ($scan->raw_ocr_result['parsed_items'] ?? [])
                : [],
            'error'  => $scan->error_message,
        ]);
    }

    public function confirm(Request $request, Bill $bill, ReceiptScan $scan)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $request->validate([
            'items'            => 'required|array',
            'items.*.name'     => 'required|string',
            'items.*.price'    => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        foreach ($request->items as $item) {
            $bill->items()->create($item);
        }

        return redirect()->route('bills.show', $bill)->with('success', 'Item dari struk berhasil ditambahkan!');
    }

    public function destroy(Bill $bill, ReceiptScan $scan)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        Storage::disk('public')->delete($scan->image_path);
        $scan->delete();
        return back()->with('success', 'Scan dihapus.');
    }
}
