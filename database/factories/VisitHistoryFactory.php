<?php

namespace Database\Factories;

use App\Models\VisitHistory;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitHistoryFactory extends Factory
{
    protected $model = VisitHistory::class;

    public function definition(): array
    {
        return [
            'visitor_id' => Visitor::factory(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'check_in_time' => fake()->dateTimeBetween('-1 month', 'now'),
            'check_out_time' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'expected_duration' => fake()->randomElement(['1 hour', '2 hours', '3 hours', '4 hours', 'Full day']),
        ];
    }
}
