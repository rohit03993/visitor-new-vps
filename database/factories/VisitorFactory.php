<?php

namespace Database\Factories;

use App\Models\Visitor;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Visitor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'purpose' => fake()->sentence(3),
            'location_id' => Location::factory(),
            'expected_duration' => fake()->randomElement(['1 hour', '2 hours', '3 hours', '4 hours', 'Full day']),
        ];
    }
}
