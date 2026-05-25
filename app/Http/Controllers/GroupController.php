<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Auth::user()->groups()->withCount('members')->latest()->get();
        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        return view('groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = Group::create([...$validated, 'created_by' => Auth::id()]);

        $group->members()->attach(Auth::id(), ['role' => 'admin', 'joined_at' => now()]);

        return redirect()->route('groups.show', $group)->with('success', 'Grup berhasil dibuat!');
    }

    public function show(Group $group)
    {
        abort_unless($group->hasMember(Auth::user()), 403, 'Kamu bukan anggota grup ini.');
        $group->load(['members', 'bills' => fn($q) => $q->withCount('items')->latest()]);
        return view('groups.show', compact('group'));
    }

    public function edit(Group $group)
    {
        abort_unless($group->isAdmin(Auth::user()), 403, 'Hanya admin yang bisa mengedit grup.');
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
        abort_unless($group->isAdmin(Auth::user()), 403);
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        $group->update($validated);
        return redirect()->route('groups.show', $group)->with('success', 'Grup diperbarui.');
    }

    public function destroy(Group $group)
    {
        abort_unless(Auth::id() === $group->created_by, 403, 'Hanya pemilik yang bisa menghapus grup.');
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Grup dihapus.');
    }

    public function invite(Request $request, Group $group)
    {
        abort_unless($group->isAdmin(Auth::user()), 403);
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($group->hasMember($user)) {
            return back()->withErrors(['email' => 'Pengguna sudah menjadi anggota.']);
        }

        $group->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
        return back()->with('success', $user->name . ' berhasil diundang.');
    }

    public function removeMember(Group $group, User $user)
    {
        abort_unless($group->isAdmin(Auth::user()), 403);
        if ($user->id === $group->created_by) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus pemilik grup.']);
        }
        $group->members()->detach($user->id);
        return back()->with('success', 'Anggota dihapus.');
    }
}
