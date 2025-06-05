<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $data = [
        [

        ],
    ];
    private UserCollection $collection;
    private string $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = $this->baseUri . "/users";
    }

    public function test_user_exists(): void
    {
        $this->assertTrue( class_exists( User::class ), "User class has not been created." );
    }

    public function test_user_attributes(): void
    {
        $user = User::factory()->create();
        $userAttributes = $user->getAttributes(); 

        $this->assertArrayHasKey( "name", $userAttributes, "name attribute was not found in user model" );
        $this->assertArrayHasKey( "email", $userAttributes, "email attribute was not found in user model" );
        $this->assertArrayHasKey( "email_verified_at", $userAttributes, "email_verified_at attribute was not found in user model" );
        $this->assertArrayHasKey( "password", $userAttributes, "password attribute was not found in user model" );
        $this->assertArrayHasKey( "remember_token", $userAttributes, "remember_token attribute was not found in user model" );
    }

    public function test_user_controller_exists(): void
    {
        $this->assertTrue( class_exists( UserController::class ), "UserController class has not been created." );
    }

    public function test_user_controller_methods_exist(): void
    {
        $this->assertTrue( method_exists( UserController::class, "delete" ), "The method 'delete' does not exist in the UserController class" );
        $this->assertTrue( method_exists( UserController::class, "index" ), "The method 'index' does not exist in the UserController class" );
        $this->assertTrue( method_exists( UserController::class, "show" ), "The method 'show' does not exist in the UserController class" );
        $this->assertTrue( method_exists( UserController::class, "store" ), "The method 'store' does not exist in the UserController class" );
    }

    public function test_user_repository_exists(): void
    {
        $this->assertTrue( class_exists( UserRepository::class ), "UserRepository class has not been created." );
    }

    public function test_user_repository_methods_exist(): void
    {
        $this->assertTrue( method_exists( UserRepository::class, "create" ), "The method 'create' does not exist in the UserRepository class" );
        $this->assertTrue( method_exists( UserRepository::class, "delete" ), "The method 'delete' does not exist in the UserRepository class" );
        $this->assertTrue( method_exists( UserRepository::class, "exists" ), "The method 'exists' does not exist in the UserRepository class" );
        $this->assertTrue( method_exists( UserRepository::class, "get" ), "The method 'get' does not exist in the UserRepository class" );
        $this->assertTrue( method_exists( UserRepository::class, "list" ), "The method 'list' does not exist in the UserRepository class" );
    }

    public function test_get_users(): void
    {

    }
}
