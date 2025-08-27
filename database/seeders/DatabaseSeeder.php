<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test1@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory(10)->create();

        Event::factory()->create([
            'name' => 'Open event',
            'capacity' => 10,
        ]);

        Event::factory()->create([
            'name' => 'Limited event',
            'capacity' => 2,
        ]);

        Event::factory()->create([
            'name' => 'Solo event',
            'capacity' => 1,
        ]);

        Event::factory()->create([
            'name' => 'Closed event',
            'capacity' => 0,
        ]);
    }
}
