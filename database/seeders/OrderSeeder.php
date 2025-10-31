<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least 10 users to link orders to
        $users = User::factory()->count(10)->create();

        // Create 50 orders distributed among the created users
        Order::factory()
            ->count(50)
            ->recycle($users) // Recycle the existing users
            ->create();
            
        $this->command->info('OrderSeeder finished: 50 orders created for 10 users.');
    }
}