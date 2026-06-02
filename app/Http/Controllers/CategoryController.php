<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())
            ->withCount('bills')
            ->latest()
            ->get();

        $categories->each(function ($cat) {
            // Only count bills that still exist (not soft-deleted)
            $cat->total_spent = $cat->bills()->whereNotNull('bills.id')->get()
                ->sum(fn($b) => $b->grand_total);
            $cat->bills_count = $cat->bills()->count();
        });

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        Category::create([...$validated, 'user_id' => Auth::id()]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $validated['name'] . '" berhasil dibuat!');
    }

    // ── Category dashboard ────────────────────────────────────────────────────

    public function dashboard()
    {
        $categories = Category::where('user_id', Auth::id())
            ->with(['bills' => fn($q) => $q->with('group')])
            ->latest()
            ->get();

        // Clean up bills that still exist and attach computed totals
        foreach ($categories as $cat) {
            $cat->setRelation('bills', $cat->bills->filter(fn($b) => $b->group !== null));
        }

        // Overall stats
        $allBills = $categories->flatMap(fn($c) => $c->bills)->unique('id');
        $totalSpentAll  = $allBills->sum(fn($b) => $b->grand_total);
        $totalBillsAll  = $allBills->count();

        // Monthly breakdown (last 6 months)
        $monthly = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthBills = $allBills->filter(
                fn($b) => $b->date->format('Y-m') === $month->format('Y-m')
            );
            $monthly->push([
                'label'  => $month->translatedFormat('M Y'),
                'total'  => $monthBills->sum(fn($b) => $b->grand_total),
                'count'  => $monthBills->count(),
            ]);
        }

        // Per-category totals for pie breakdown
        $categoryTotals = $categories->map(fn($cat) => [
            'name'   => $cat->name,
            'icon'   => $cat->icon,
            'color'  => $cat->color,
            'total'  => $cat->bills->sum(fn($b) => $b->grand_total),
            'count'  => $cat->bills->count(),
        ])->filter(fn($c) => $c['total'] > 0)->sortByDesc('total')->values();

        // Recent bills across all categories
        $recentBills = $allBills->sortByDesc('date')->take(10);

        return view('categories.dashboard', compact(
            'categories', 'totalSpentAll', 'totalBillsAll',
            'monthly', 'categoryTotals', 'recentBills'
        ));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function show(Category $category)
    {
        abort_unless($category->user_id === Auth::id(), 403);

        // Only load bills whose group still exists
        $bills = $category->bills()
            ->with('group')
            ->get()
            ->filter(fn($b) => $b->group !== null)
            ->sortByDesc('date');

        $totalSpent = $bills->sum(fn($b) => $b->grand_total);

        return view('categories.show', compact('category', 'bills', 'totalSpent'));
    }

    public function edit(Category $category)
    {
        abort_unless($category->user_id === Auth::id(), 403);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($category->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'        => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        abort_unless($category->user_id === Auth::id(), 403);
        $name = $category->name;
        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $name . '" dihapus.');
    }

    public function attachBill(Request $request, Category $category)
    {
        abort_unless($category->user_id === Auth::id(), 403);
        $request->validate(['bill_id' => 'required|exists:bills,id']);
        $category->bills()->syncWithoutDetaching([$request->bill_id]);
        return back()->with('success', 'Tagihan ditambahkan ke kategori.');
    }

    public function detachBill(Category $category, Bill $bill)
    {
        abort_unless($category->user_id === Auth::id(), 403);
        $category->bills()->detach($bill->id);
        return back()->with('success', 'Tagihan dihapus dari kategori.');
    }
}
