<?php

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Auth tests ────────────────────────────────────────────────────────────────

test('user can register', function () {
    $response = $this->post(route('register'), [
        'name'                  => 'Ayya Fitriana',
        'email'                 => 'ayya@test.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['email' => 'ayya@test.com']);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('correct')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])
         ->assertSessionHasErrors('email');
});

test('unauthenticated user is redirected to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

// ── Group tests ───────────────────────────────────────────────────────────────

test('user can create a group', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('groups.store'), ['name' => 'Tim Makan', 'description' => 'Test'])
         ->assertRedirect();

    $this->assertDatabaseHas('groups', ['name' => 'Tim Makan', 'created_by' => $user->id]);
    $this->assertDatabaseHas('group_members', ['user_id' => $user->id, 'role' => 'admin']);
});

test('non-member cannot view group', function () {
    $owner   = User::factory()->create();
    $outsider = User::factory()->create();

    $group = Group::factory()->create(['created_by' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => 'admin', 'joined_at' => now()]);

    $this->actingAs($outsider)
         ->get(route('groups.show', $group))
         ->assertForbidden();
});

// ── Bill tests ────────────────────────────────────────────────────────────────

test('member can create a bill', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->create(['created_by' => $user->id]);
    $group->members()->attach($user->id, ['role' => 'admin', 'joined_at' => now()]);

    $this->actingAs($user)->post(route('bills.store', $group), [
        'name'            => 'Makan Siang',
        'date'            => now()->toDateString(),
        'tax_percent'     => 10,
        'service_percent' => 5,
    ])->assertRedirect();

    $this->assertDatabaseHas('bills', ['name' => 'Makan Siang', 'group_id' => $group->id]);
});

test('member can add item to bill', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->create(['created_by' => $user->id]);
    $group->members()->attach($user->id, ['role' => 'admin', 'joined_at' => now()]);
    $bill  = Bill::factory()->create(['group_id' => $group->id, 'created_by' => $user->id]);

    $this->actingAs($user)->post(route('bills.items.store', $bill), [
        'name'     => 'Nasi Goreng',
        'price'    => 25000,
        'quantity' => 1,
    ])->assertRedirect();

    $this->assertDatabaseHas('bill_items', ['bill_id' => $bill->id, 'name' => 'Nasi Goreng']);
});

test('equal split calculation is saved correctly', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->create(['created_by' => $user->id]);
    $group->members()->attach($user->id, ['role' => 'admin', 'joined_at' => now()]);
    $bill  = Bill::factory()->create(['group_id' => $group->id, 'created_by' => $user->id, 'tax_percent' => 0, 'service_percent' => 0]);

    BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 100000, 'quantity' => 1]);
    Participant::factory()->create(['bill_id' => $bill->id, 'name' => $user->name, 'user_id' => $user->id]);
    Participant::factory()->create(['bill_id' => $bill->id, 'name' => 'Bob']);

    $this->actingAs($user)->post(route('bills.calculate', $bill), ['method' => 'equal'])
         ->assertRedirect(route('bills.show', $bill));

    $this->assertDatabaseCount('split_results', 2);
});

// ── Share link tests ──────────────────────────────────────────────────────────

test('share link can be generated and accessed publicly', function () {
    $user  = User::factory()->create();
    $group = Group::factory()->create(['created_by' => $user->id]);
    $group->members()->attach($user->id, ['role' => 'admin', 'joined_at' => now()]);
    $bill  = Bill::factory()->create(['group_id' => $group->id, 'created_by' => $user->id]);

    $this->actingAs($user)->post(route('share.generate', $bill));

    $link = $bill->sharedLinks()->where('is_active', true)->first();
    expect($link)->not->toBeNull();

    // Public access (no auth)
    $this->get(route('share.view', $link->token))->assertOk();
});
