<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = Tag::factory()
            ->count(8)
            ->create();

        $listings = Listing::all();

        foreach ($listings as $listing) {
            $tagIds = $tags->random(rand(1, 3))
                ->pluck('id');
            $listing->tags()->attach($tagIds);
        }
    }
}
