<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "access_level" => fake()->randomNumber( 2, false ),
            "code" => mb_strtoupper( fake()->lexify( "??????????" ) ),
            "name" => fake()->lexify( "role-??????????" )
        ];
    }
}
