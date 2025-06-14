<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "code" => mb_strtoupper( fake()->lexify( "??????????" ) ),
            "name" => "Fake Category",
            "type" => fake()->randomElement( [ "Expense", "Revenue" ] ),
            "user_id" => function() {
                return User::factory()->create()->getAttribute( "id" );
            },
        ];
    }
}
