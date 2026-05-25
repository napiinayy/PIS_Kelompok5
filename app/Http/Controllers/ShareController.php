<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Export;
use App\Models\SharedLink;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShareController extends Controller
{
    public function generate(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $link = SharedLink::generateFor($bill);
        return back()->with('success', 'Link dibuat: ' . route('share.view', $link->token));
    }

    public function view(string $token)
    {
        $link = SharedLink::where('token', $token)->firstOrFail();
        abort_unless($link->isValid(), 404);

        $bill = $link->bill->load(['items', 'participants.splitResult', 'group']);
        return view('share.public', compact('bill', 'link'));
    }

    public function deactivate(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $bill->sharedLinks()->update(['is_active' => false]);
        return back()->with('success', 'Link dinonaktifkan.');
    }

    public function markPaid(Bill $bill, \App\Models\Participant $participant)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $participant->update(['is_paid' => !$participant->is_paid]);
        return back();
    }
}

class ExportController extends Controller
{
    public function pdf(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $bill->load(['items', 'participants.splitResult', 'group']);

        $pdf = Pdf::loadView('exports.bill-pdf', compact('bill'));
        $filename = 'bill-' . $bill->id . '-' . now()->format('Ymd') . '.pdf';
        $path = 'exports/' . $filename;

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
