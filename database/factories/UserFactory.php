<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "current_balance" => fake()->randomFloat( 2 ),
            "email" => fake()->unique()->safeEmail(),
            "email_verified_at" => date_create()->format( "Y-m-d H:i:s" ),
            "first_name" => fake()->firstName(),
            "id" => mb_strtoupper( fake()->lexify( "????????" ) ),
            "language" => "en-us",
            "last_name" => fake()->lastName(),
            "last_login_date" => date_create()->format( "Y-m-d H:i:s" ),
            "last_password_update" => date_create()->format( "Y-m-d H:i:s" ),
            "password" => static::$password ??= Hash::make( "password" ),
            "password_expires_on" => date_create()->format( "Y-m-d H:i:s" ),
            "registration_date" => date_create()->format( "Y-m-d H:i:s" ),
            "remember_token" => Str::random( 10 ),
            "role" => function() {
                return Role::factory()->create()->getAttribute( "code" );
            },
            "salt" => base64_encode( random_bytes( config( "security.default_salt_byte_length" ) ) ),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            "email_verified_at" => null,
        ]);
    }
}
