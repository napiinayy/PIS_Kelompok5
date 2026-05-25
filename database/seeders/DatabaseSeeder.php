<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo users
        $users = [
            ['name' => 'Raynanda Putra Wijaya', 'email' => 'raynanda@demo.com'],
            ['name' => 'Ayya Fitriana Nafik',   'email' => 'ayya@demo.com'],
            ['name' => 'Ridanar Permana Putra',  'email' => 'ridanar@demo.com'],
            ['name' => 'Aron Ernesto Siregar',   'email' => 'aron@demo.com'],
        ];

        $createdUsers = collect($users)->map(fn($u) => User::firstOrCreate(
            ['email' => $u['email']],
            ['name' => $u['name'], 'password' => Hash::make('password123')]
        ));

        // Demo group
        $group = Group::firstOrCreate(
            ['name' => 'Kelompok 5 SI4803'],
            ['description' => 'Demo group for Split Bill project', 'created_by' => $createdUsers->first()->id]
        );

        foreach ($createdUsers as $i => $user) {
            if (!$group->hasMember($user)) {
                $group->members()->attach($user->id, [
                    'role'      => $i === 0 ? 'admin' : 'member',
                    'joined_at' => now(),
                ]);
            }
        }

        // Demo bill
        $bill = Bill::firstOrCreate(
            ['name' => 'Makan Siang Demo', 'group_id' => $group->id],
            [
                'created_by'      => $createdUsers->first()->id,
                'restaurant_name' => 'Warteg Barokah',
                'date'            => now()->toDateString(),
                'tax_percent'     => 10,
                'service_percent' => 5,
                'status'          => 'draft',
            ]
        );

        // Items
        $items = [
            ['name' => 'Nasi Goreng Spesial', 'price' => 25000, 'quantity' => 1],
            ['name' => 'Ayam Bakar',           'price' => 35000, 'quantity' => 2],
            ['name' => 'Es Teh Manis',         'price' => 8000,  'quantity' => 4],
            ['name' => 'Jus Alpukat',          'price' => 15000, 'quantity' => 2],
        ];

        if ($bill->items()->count() === 0) {
            foreach ($items as $item) {
                $bill->items()->create($item);
            }
        }

        // Participants
        if ($bill->participants()->count() === 0) {
            foreach ($createdUsers as $user) {
                $bill->participants()->create(['user_id' => $user->id, 'name' => $user->name]);
            }
        }

        $this->command->info('✅ Demo data seeded!');
        $this->command->info('Login: ayya@demo.com / password123');
    }
}
