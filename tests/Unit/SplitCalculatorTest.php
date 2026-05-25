<?php

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Group;
use App\Models\ItemAssignment;
use App\Models\Participant;
use App\Models\User;
use App\Services\SplitCalculatorService;

beforeEach(function () {
    $this->calculator = new SplitCalculatorService();
});

test('equal split divides grand total evenly', function () {
    $bill = Bill::factory()->create([
        'tax_percent'     => 10,
        'service_percent' => 5,
    ]);
    BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 100000, 'quantity' => 1]);
    BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 50000,  'quantity' => 2]);

    $p1 = Participant::factory()->create(['bill_id' => $bill->id, 'name' => 'Alice']);
    $p2 = Participant::factory()->create(['bill_id' => $bill->id, 'name' => 'Bob']);

    $bill->load('items', 'participants');
    $results = $this->calculator->calculateEqual($bill);

    // Grand total = 200000 * 1.15 = 230000, per person = 115000
    expect($results->sum('total'))->toBe($bill->grand_total);
    expect($results->count())->toBe(2);
});

test('proportional split assigns correct shares', function () {
    $bill = Bill::factory()->create([
        'tax_percent'     => 10,
        'service_percent' => 0,
    ]);

    $item1 = BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 50000, 'quantity' => 1]);
    $item2 = BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 30000, 'quantity' => 1]);

    $p1 = Participant::factory()->create(['bill_id' => $bill->id, 'name' => 'Alice']);
    $p2 = Participant::factory()->create(['bill_id' => $bill->id, 'name' => 'Bob']);

    // Alice gets item1, Bob gets item2
    ItemAssignment::create(['bill_item_id' => $item1->id, 'participant_id' => $p1->id, 'qty_portion' => 1]);
    ItemAssignment::create(['bill_item_id' => $item2->id, 'participant_id' => $p2->id, 'qty_portion' => 1]);

    $bill->load('items.assignments.participant', 'participants');
    $results = $this->calculator->calculateProportional($bill);

    $alice = $results->firstWhere(fn($r) => $r['participant']->id === $p1->id);
    $bob   = $results->firstWhere(fn($r) => $r['participant']->id === $p2->id);

    expect($alice['subtotal'])->toBe(50000.0);
    expect($bob['subtotal'])->toBe(30000.0);
    expect($results->sum('total'))->toBe($bill->grand_total);
});

test('rounding difference does not exceed 1 rupiah', function () {
    $bill = Bill::factory()->create(['tax_percent' => 10, 'service_percent' => 5]);
    BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 10000, 'quantity' => 1]);

    Participant::factory()->count(3)->create(['bill_id' => $bill->id]);

    $bill->load('items', 'participants');
    $results = $this->calculator->calculateEqual($bill);

    $diff = abs($results->sum('total') - $bill->grand_total);
    expect($diff)->toBeLessThanOrEqual(1.0);
});

test('equal split with zero participants returns empty collection', function () {
    $bill = Bill::factory()->create();
    BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 50000]);

    $bill->load('items', 'participants');
    $results = $this->calculator->calculateEqual($bill);

    expect($results)->toBeEmpty();
});
