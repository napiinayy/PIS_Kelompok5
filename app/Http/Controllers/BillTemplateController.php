<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillTemplate;
use App\Models\BillTemplateItem;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillTemplateController extends Controller
{
    // READ — list all templates for current user
    public function index()
    {
        $templates = BillTemplate::where('user_id', Auth::id())
            ->withCount('items')
            ->orderByDesc('times_used')
            ->latest()
            ->get();

        return view('templates.index', compact('templates'));
    }

    // CREATE — show form
    public function create()
    {
        return view('templates.create');
    }

    // CREATE — store template + items
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'restaurant_name' => 'nullable|string|max:255',
            'tax_percent'     => 'nullable|numeric|min:0|max:100',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:1000',
            'items'           => 'required|array|min:1',
            'items.*.name'    => 'required|string|max:255',
            'items.*.price'   => 'required|numeric|min:0',
            'items.*.quantity'=> 'required|integer|min:1',
        ]);

        $template = BillTemplate::create([
            'user_id'         => Auth::id(),
            'name'            => $validated['name'],
            'restaurant_name' => $validated['restaurant_name'] ?? null,
            'tax_percent'     => $validated['tax_percent'] ?? 0,
            'service_percent' => $validated['service_percent'] ?? 0,
            'notes'           => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $template->items()->create($item);
        }

        return redirect()->route('templates.index')
            ->with('success', 'Template "' . $template->name . '" berhasil disimpan!');
    }

    // READ — detail
    public function show(BillTemplate $template)
    {
        abort_unless($template->user_id === Auth::id(), 403);
        $template->load('items');
        return view('templates.show', compact('template'));
    }

    // UPDATE — show form
    public function edit(BillTemplate $template)
    {
        abort_unless($template->user_id === Auth::id(), 403);
        $template->load('items');
        return view('templates.edit', compact('template'));
    }

    // UPDATE — save
    public function update(Request $request, BillTemplate $template)
    {
        abort_unless($template->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'restaurant_name' => 'nullable|string|max:255',
            'tax_percent'     => 'nullable|numeric|min:0|max:100',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:1000',
            'items'           => 'required|array|min:1',
            'items.*.name'    => 'required|string|max:255',
            'items.*.price'   => 'required|numeric|min:0',
            'items.*.quantity'=> 'required|integer|min:1',
        ]);

        $template->update([
            'name'            => $validated['name'],
            'restaurant_name' => $validated['restaurant_name'] ?? null,
            'tax_percent'     => $validated['tax_percent'] ?? 0,
            'service_percent' => $validated['service_percent'] ?? 0,
            'notes'           => $validated['notes'] ?? null,
        ]);

        // Replace all items
        $template->items()->delete();
        foreach ($validated['items'] as $item) {
            $template->items()->create($item);
        }

        return redirect()->route('templates.index')
            ->with('success', 'Template berhasil diperbarui.');
    }

    // DELETE
    public function destroy(BillTemplate $template)
    {
        abort_unless($template->user_id === Auth::id(), 403);
        $name = $template->name;
        $template->delete();
        return redirect()->route('templates.index')
            ->with('success', 'Template "' . $name . '" dihapus.');
    }

    // Save existing bill AS a new template
    public function saveFromBill(Request $request)
    {
        $request->validate(['bill_id' => 'required|exists:bills,id']);

        $bill = Bill::with('items')->findOrFail($request->bill_id);
        abort_unless($bill->group->hasMember(Auth::user()), 403);

        $template = BillTemplate::create([
            'user_id'         => Auth::id(),
            'name'            => $bill->name . ' (Template)',
            'restaurant_name' => $bill->restaurant_name,
            'tax_percent'     => $bill->tax_percent,
            'service_percent' => $bill->service_percent,
            'notes'           => $bill->notes,
        ]);

        foreach ($bill->items as $item) {
            $template->items()->create([
                'name'     => $item->name,
                'price'    => $item->price,
                'quantity' => $item->quantity,
            ]);
        }

        return redirect()->route('templates.show', $template)
            ->with('success', 'Tagihan berhasil disimpan sebagai template!');
    }

    // USE template — show group picker before creating bill
    public function usePage(BillTemplate $template)
    {
        abort_unless($template->user_id === Auth::id(), 403);
        $template->load('items');
        $groups = Auth::user()->groups()->get();
        return view('templates.use', compact('template', 'groups'));
    }

    // USE template — actually create the bill from template
    public function useTemplate(Request $request, BillTemplate $template)
    {
        abort_unless($template->user_id === Auth::id(), 403);

        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'date'     => 'required|date',
            'name'     => 'required|string|max:255',
        ]);

        $group = Group::findOrFail($request->group_id);
        abort_unless($group->hasMember(Auth::user()), 403);

        $template->load('items');

        // Create the bill
        $bill = $group->bills()->create([
            'created_by'      => Auth::id(),
            'name'            => $request->name,
            'restaurant_name' => $template->restaurant_name,
            'date'            => $request->date,
            'tax_percent'     => $template->tax_percent,
            'service_percent' => $template->service_percent,
            'notes'           => $template->notes,
        ]);

        // Copy template items
        foreach ($template->items as $item) {
            $bill->items()->create([
                'name'     => $item->name,
                'price'    => $item->price,
                'quantity' => $item->quantity,
            ]);
        }

        // Auto-add group members as participants
        foreach ($group->members as $member) {
            $bill->participants()->create(['user_id' => $member->id, 'name' => $member->name]);
        }

        // Increment usage counter
        $template->increment('times_used');

        return redirect()->route('bills.items.page', $bill)
            ->with('success', 'Tagihan dibuat dari template "' . $template->name . '"!');
    }
}
