<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Group;
use App\Models\Participant;
use App\Services\SplitCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function __construct(private SplitCalculatorService $calculator) {}

    // ── Step 1: Create ────────────────────────────────────────────────────────

    public function create(Group $group)
    {
        abort_unless($group->hasMember(Auth::user()), 403);
        return view('bills.create', compact('group'));
    }

    public function store(Request $request, Group $group)
    {
        abort_unless($group->hasMember(Auth::user()), 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'restaurant_name' => 'nullable|string|max:255',
            'date'            => 'required|date',
            'tax_percent'     => 'nullable|numeric|min:0|max:100',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $bill = $group->bills()->create([...$validated, 'created_by' => Auth::id()]);

        foreach ($group->members as $member) {
            $bill->participants()->create(['user_id' => $member->id, 'name' => $member->name]);
        }

        return redirect()->route('bills.items.page', $bill)
            ->with('success', 'Tagihan dibuat! Sekarang tambahkan item pesanan.');
    }

    // ── Step 2: Items page ────────────────────────────────────────────────────

    public function itemsPage(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        abort_if($bill->status === 'settled', 403, 'Tagihan sudah ditutup.');
        $bill->load(['items', 'participants', 'scans', 'group']);
        return view('bills.items', compact('bill'));
    }

    // ── Step 3: Split page ────────────────────────────────────────────────────

    public function splitPage(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $bill->load([
            'items.assignments.participant',
            'participants.splitResult',
            'sharedLinks' => fn($q) => $q->where('is_active', true),
            'group',
        ]);
        return view('bills.split', compact('bill'));
    }

    // ── Overview (settled bills) ──────────────────────────────────────────────

    public function show(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $bill->load([
            'items',
            'participants.splitResult',
            'sharedLinks' => fn($q) => $q->where('is_active', true),
            'group',
        ]);
        return view('bills.show', compact('bill'));
    }

    public function edit(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        abort_if($bill->status === 'settled', 403, 'Tagihan sudah ditutup, tidak bisa diedit.');
        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        abort_if($bill->status === 'settled', 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'restaurant_name' => 'nullable|string|max:255',
            'date'            => 'required|date',
            'tax_percent'     => 'nullable|numeric|min:0|max:100',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $bill->update($validated);
        return redirect()->route('bills.items.page', $bill)->with('success', 'Tagihan diperbarui.');
    }

    public function destroy(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $group = $bill->group;
        $bill->delete();
        return redirect()->route('groups.show', $group)->with('success', 'Tagihan dihapus.');
    }

    // ── Settle / Close bill ───────────────────────────────────────────────────

    public function settle(Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        abort_unless($bill->status === 'calculated', 400, 'Tagihan harus sudah dihitung sebelum ditutup.');

        // Mark all remaining participants as paid
        $bill->participants()->update(['is_paid' => true]);
        $bill->update(['status' => 'settled']);

        return redirect()->route('groups.show', $bill->group)
            ->with('success', '🎉 Tagihan "' . $bill->name . '" berhasil ditutup!');
    }

    // ── Items CRUD ────────────────────────────────────────────────────────────

    public function storeItem(Request $request, Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        abort_if($bill->status === 'settled', 403);
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);
        $bill->items()->create($validated);
        return back()->with('success', 'Item ditambahkan.');
    }

    public function updateItem(Request $request, Bill $bill, BillItem $item)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);
        $item->update($validated);
        return back()->with('success', 'Item diperbarui.');
    }

    public function destroyItem(Bill $bill, BillItem $item)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $item->delete();
        return back()->with('success', 'Item dihapus.');
    }

    // ── Participants ──────────────────────────────────────────────────────────

    public function storeParticipant(Request $request, Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $bill->participants()->create($validated);
        return back()->with('success', 'Peserta ditambahkan.');
    }

    public function destroyParticipant(Bill $bill, Participant $participant)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $participant->delete();
        return back()->with('success', 'Peserta dihapus.');
    }

    // ── Assign ────────────────────────────────────────────────────────────────

    public function assign(Request $request, Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);

        $assignments = $request->input('assignments', []);

        foreach ($bill->items as $item) {
            $item->assignments()->delete();
            $participantIds = $assignments[$item->id] ?? [];
            foreach ($participantIds as $participantId) {
                $item->assignments()->create([
                    'participant_id' => $participantId,
                    'qty_portion'    => 1,
                ]);
            }
        }

        return back()->with('success', 'Assignment berhasil disimpan.');
    }

    // ── Calculate ─────────────────────────────────────────────────────────────

    public function calculate(Request $request, Bill $bill)
    {
        abort_unless($bill->group->hasMember(Auth::user()), 403);
        $method = $request->input('method', 'proportional');

        $results = $method === 'equal'
            ? $this->calculator->calculateEqual($bill->load('items', 'participants'))
            : $this->calculator->calculateProportional($bill->load('items.assignments.participant', 'participants'));

        $this->calculator->save($bill, $results);

        return redirect()->route('bills.split.page', $bill)
            ->with('success', 'Pembagian tagihan berhasil dihitung!');
    }
}
