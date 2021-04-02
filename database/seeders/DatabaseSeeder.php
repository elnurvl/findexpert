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
        $user = createUser(1, [
            'name' => 'Elnur Hajiyev',
            'email' => 'elnur@findexpert.test',
            'password' => bcrypt('password')
        ]);

        $users = createUser(3);

        attachUser($users[0], $user);
        attachUser($user, $users[0]);

        Topic::factory()->count(3)->create([
            'user_id' => $user,
        ]);
    }
}
