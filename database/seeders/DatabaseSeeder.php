<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $user = User::factory()->create([
            'name' => 'Elnur Hajiyev',
            'email' => 'elnur@findexpert.test',
            'password' => bcrypt('password')
        ]);

        Topic::factory()->count(3)->create([
            'user_id' => $user,
        ]);
    }
}
