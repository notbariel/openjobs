<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Storage::deleteDirectory('.cache');
        Storage::deleteDirectory('logos');

        $users = User::all();

        foreach ($users as $user) {
            Company::factory()
                ->count(rand(1, 2))
                ->for($user, 'user')
                ->create();
        }
    }
}
