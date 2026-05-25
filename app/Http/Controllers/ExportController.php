<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Export;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function pdf(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $bill->load(['items', 'participants.splitResult', 'group']);

        $pdf      = Pdf::loadView('exports.bill-pdf', compact('bill'));
        $filename = 'bill-' . $bill->id . '-' . now()->format('Ymd') . '.pdf';
        $path     = 'exports/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        Export::create([
            'bill_id'    => $bill->id,
            'created_by' => Auth::id(),
            'type'       => 'pdf',
            'file_path'  => $path,
        ]);

        return $pdf->download($filename);
    }
}
