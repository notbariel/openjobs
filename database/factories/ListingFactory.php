<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $minSalary = array_slice(Listing::salaryRange(), 0, 16);
        $maxSalary = array_slice(Listing::salaryRange(), 16);

        $jobTitle = $this->faker->jobTitle();

        $description = '';
        for ($i = 1; $i < rand(6, 10); $i++) {
            $description .= '<p>' . implode(' ', $this->faker->sentences(rand(3, 6))) . '</p>';
        }

        $isPinned = $this->faker->boolean();

        return [
            'employment_type' => Arr::random(EmploymentType::getValues()),

            'position' => $jobTitle,
            'location' => $this->faker->boolean() ? 'Worldwide' : $this->faker->country(),
            'description' => $description,

            'is_highlighted' => $this->faker->boolean(),
            'is_pinned' => $isPinned,

            'min_annual_salary' => Arr::random($minSalary),
            'max_annual_salary' => Arr::random($maxSalary),

            'apply_url' => $this->faker->url(),

            'is_closed' => $isPinned ? false : $this->faker->boolean(),

            'paid_at' => !$isPinned ?
                $this->faker->dateTimeBetween('-6 months', 'now') :
                $this->faker->dateTimeBetween('-2 days', 'now'),
        ];
    }
}
