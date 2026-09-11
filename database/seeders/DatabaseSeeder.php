<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Creates the one dashboard account described in config/dashboard.php.
     * Safe to re-run: it updates the existing account rather than failing on
     * the unique username.
     */
    public function run(): void
    {
        $account = config('dashboard.seed_user');

        User::updateOrCreate(
            ['username' => $account['username']],
            [
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => $account['password'],
            ]
        );

        $this->command?->info("Dashboard account ready: {$account['username']}");
    }
}
