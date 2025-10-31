<?php

namespace Database\Seeders;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users (assuming UserSeeder runs first)
        $users = User::all();

        if ($users->isEmpty()) {
            echo "Warning: No users found. Skipping DeviceToken seeding.\n";
            return;
        }

        // Create 2 device tokens for each existing user
        $users->each(function (User $user) {
            DeviceToken::factory()->count(2)->for($user)->create();
        });
        
        echo "Created device tokens for " . $users->count() . " users.\n";
    }
}