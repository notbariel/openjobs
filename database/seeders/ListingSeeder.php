<?php

namespace Database\Seeders;

use App\Models\Click;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            Listing::factory()
                ->count(rand(1, 5))
                ->for($user, 'user')
                ->for($user->company, 'company')
                ->has(
                    Click::factory()->count(rand(10, 50)),
                    'clicks'
                )
                ->create();
        }
    }
}
