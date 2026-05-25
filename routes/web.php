<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'))->name('home');
Route::get('/s/{token}', [ShareController::class, 'view'])->name('share.view');

// ── Guest ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        $user        = auth()->user();
        $groups      = $user->groups()->withCount('members')->latest()->take(5)->get();
        $recentBills = \App\Models\Bill::whereIn('group_id', $user->groups->pluck('id'))
            ->with('group')->latest()->take(5)->get();
        return view('dashboard', compact('groups', 'recentBills'));
    })->name('dashboard');

    // Profile
    Route::get('/profile',          [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile',          [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // ── Groups ────────────────────────────────────────────────────────────────
    // Bill create routes BEFORE resource to prevent conflict
    Route::get('/groups/{group}/bills/create', [BillController::class, 'create'])->name('bills.create');
    Route::post('/groups/{group}/bills',        [BillController::class, 'store'])->name('bills.store');

    Route::resource('groups', GroupController::class);
    Route::post('/groups/{group}/invite',           [GroupController::class, 'invite'])->name('groups.invite');
    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove');

    // ── Bills — 3-step flow ───────────────────────────────────────────────────
    Route::get('/bills/{bill}/items', [BillController::class, 'itemsPage'])->name('bills.items.page');
    Route::get('/bills/{bill}/split', [BillController::class, 'splitPage'])->name('bills.split.page');

    // Bill CRUD
    Route::get('/bills/{bill}',      [BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
    Route::put('/bills/{bill}',      [BillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}',   [BillController::class, 'destroy'])->name('bills.destroy');

    // Close / settle bill
    Route::post('/bills/{bill}/settle', [BillController::class, 'settle'])->name('bills.settle');

    // Bill items
    Route::post('/bills/{bill}/items/add',      [BillController::class, 'storeItem'])->name('bills.items.store');
    Route::put('/bills/{bill}/items/{item}',     [BillController::class, 'updateItem'])->name('bills.items.update');
    Route::delete('/bills/{bill}/items/{item}',  [BillController::class, 'destroyItem'])->name('bills.items.destroy');

    // Participants
    Route::post('/bills/{bill}/participants',                 [BillController::class, 'storeParticipant'])->name('bills.participants.store');
    Route::delete('/bills/{bill}/participants/{participant}', [BillController::class, 'destroyParticipant'])->name('bills.participants.destroy');

    // Assign & Calculate
    Route::post('/bills/{bill}/assign',    [BillController::class, 'assign'])->name('bills.assign');
    Route::post('/bills/{bill}/calculate', [BillController::class, 'calculate'])->name('bills.calculate');

    // OCR Scan
    Route::post('/bills/{bill}/scan',                [ScanController::class, 'store'])->name('scans.store');
    Route::get('/bills/{bill}/scan/{scan}/status',   [ScanController::class, 'status'])->name('scans.status');
    Route::post('/bills/{bill}/scan/{scan}/confirm', [ScanController::class, 'confirm'])->name('scans.confirm');
    Route::delete('/bills/{bill}/scan/{scan}',       [ScanController::class, 'destroy'])->name('scans.destroy');

    // Share
    Route::post('/bills/{bill}/share',              [ShareController::class, 'generate'])->name('share.generate');
    Route::post('/bills/{bill}/share/off',          [ShareController::class, 'deactivate'])->name('share.deactivate');
    Route::post('/bills/{bill}/paid/{participant}', [ShareController::class, 'markPaid'])->name('share.paid');

    // Export
    Route::get('/bills/{bill}/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
});
