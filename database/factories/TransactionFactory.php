<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "amount" => fake()->randomFloat( 2 ),
            "category" => Category::factory(),
            "description" => "This is a fake description for a transaction.",
            "date_of_transaction" => date_create()->format( "Y-m-d H:i:s" ),
            "id" => fake()->randomNumber( 5 ),
            "type" => fake()->randomElement( [ "Expense", "Revenue" ] ),
            "user_id" => User::factory(),
        ];
    }
}
