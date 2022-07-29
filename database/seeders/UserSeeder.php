<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        User::factory(10)->create();

        foreach (User::all() as $user) {
            $user->createAsStripeCustomer();
        }
    }
}
