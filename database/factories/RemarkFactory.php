<?php

namespace Database\Factories;

use App\Models\Remark;
use App\Models\VisitHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class RemarkFactory extends Factory
{
    protected $model = Remark::class;

    public function definition(): array
    {
        return [
            'visit_history_id' => VisitHistory::factory(),
            'remark' => fake()->sentence(6),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
        ];
    }
}
