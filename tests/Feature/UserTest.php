<?php

namespace Tests\Feature;

use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
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
        $this->collection = ( new UserCollection( 
            User::factory()->new()
                ->count( 5 )
                ->create()
            ) 
        );
    }

    public function test_user_exists(): void
    {
        $this->assertTrue( class_exists( User::class ), "User class has not been created." );
    }

    public function test_user_attributes(): void
    {
        $user = User::factory()->create();
        $userAttributes = $user->getAttributes(); 

        $this->assertArrayHasKey( "current_balance", $userAttributes, "current_balance attribute was not found in user model" );
        $this->assertArrayHasKey( "email", $userAttributes, "email attribute was not found in user model" );
        $this->assertArrayHasKey( "email_verified_at", $userAttributes, "email_verified_at attribute was not found in user model" );        
        $this->assertArrayHasKey( "first_name", $userAttributes, "first_name attribute was not found in user model" );
        $this->assertArrayHasKey( "id", $userAttributes, "id attribute was not found in user model" );
        $this->assertArrayHasKey( "language", $userAttributes, "language attribute was not found in user model" );
        $this->assertArrayHasKey( "last_name", $userAttributes, "last_name attribute was not found in user model" );
        $this->assertArrayHasKey( "last_login_date", $userAttributes, "last_login_date attribute was not found in user model" );
        $this->assertArrayHasKey( "last_password_update", $userAttributes, "last_password_update attribute was not found in user model" );
        $this->assertArrayHasKey( "password", $userAttributes, "password attribute was not found in user model" );
        $this->assertArrayHasKey( "password_expires_on", $userAttributes, "password_expires_on attribute was not found in user model" );
        $this->assertArrayHasKey( "registration_date", $userAttributes, "registration_date attribute was not found in user model" );
        $this->assertArrayHasKey( "remember_token", $userAttributes, "remember_token attribute was not found in user model" );        
        $this->assertArrayHasKey( "role", $userAttributes, "role attribute was not found in user model" );
        $this->assertArrayHasKey( "salt", $userAttributes, "salt attribute was not found in user model" );
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

    public function test_get_users_controller(): void
    {
        $collection = $this->collection;
        $this->mock(
            UserRepository::class,
            function ( MockInterface $mock ) use( $collection ) {
                $mock->shouldReceive( "list" )->with(
                    [
                        "page" => null,
                        "limit" => config( "pagination.default_limit" ),
                        "order" => "asc",
                        "order_by" => ""
                    ]
                )->once()->andReturn(
                    new LengthAwarePaginator(
                        $collection,
                        $collection->count(),
                        50,
                        1
                    )
                );
            }
        );

        $this->actingAs( $this->authenticatedUser )
            ->get( $this->uri )
            ->assertOk()
            ->assertJson(
                $collection->response()->getData( true )
            ) && $this->assertAuthenticated();
    }

    public function test_get_users_pagination_params_error(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "?page=x&order=inexistent&limit=0"
        )->assertInvalid( [
            "page",
            "limit",
            "order"
        ] )->assertJsonStrictire( [
            "status", 
            "message", 
            "error"
        ] );

        $this->assertAuthenticated();
        $this->assertCount( 3, $response->json()[ "errors" ] );
    }

    public function test_get_users(): void
    {
        foreach( $this->collection as $record ) {
            $record->save();
        }

        $response = $this->actingAs( $this->authenticatedUser )->get( $this->uri );
        $this->assertAuthenticated();

        $response
            ->assertOk()->assertJsonStructure( [
                'status',
                'data',
                'meta',
            ] )->assertJson( [
                'status' => true,
            ] )
            ->assertJson(
                $this->collection->response()->getData( true )
            );
    }

    public function test_get_users_unauthenticated(): void
    {
        $response = $this->get( $this->uri );
        $response->assertUnauthorized()->assertJsonStructure( [
            'message',
        ] )->assertJson( [
            'message' => $this->responseUnauthorized()->getData()->message,
        ] );
        $this->assertGuest();
    }

    public function test_store_user(): void
    {
        $userTemplate = User::factory()->create()->toArray();
        $newResource = new UserResource( $userTemplate );

        $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                // Add required user variables here
            ]
        )->assertCreated()->assertJsonStructure( [
            "status",
            "message",
            "data"
        ] )->assertJson( [ "status" => true ] )
           ->assertJson( [
                "message" => $this->responseCreated( $newResource )
                        ->getData()->message,
           ] )->assertJsonFragment( 
                $newResource->response()->getData( true )
           );
        $this->assertDatabaseHas( User::getModel(), $userTemplate );
    }

    public function test_store_user_missing_id_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "nonsense_value" => 'xxxxxx',
            ]
        );
        $response->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "status",
            "message",
            "errors",
        ] );
    }

    public function test_store_user_id_minimum_length_fail(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "id" => "0",
            ]
        );
        $response->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "status",
            "message",
            "errors",
        ] );
    }

    public function test_store_user_duplicate(): void
    {
        $this->collection[ 0 ]->save();

        $this->actingAs( $this->authenticatedUser )->post( 
            $this->uri,
            [
                "id" => $this->collection[ 0 ][ "id" ],
            ]
        )->assertJsonStructure( [
            "status",
            "message",
            "errors",
        ] )->assertConflict()
            ->assertJson( [
                "" => $this->responseDuplicate()->getData()->message,
            ] );
    }

    public function test_store_user_unauthenticated(): void
    {
        $response = $this->post( $this->uri, $this->collection[ 0 ] );
        $response->assertUnauthorized()
            ->assertJsonStructure( [
                "status",
                "message",
                "errors",
            ] )
            ->assertJson( [
                "status" => false,
            ] )
            ->assertJson( [
                "message" => $this->responseUnauthorized()->getData()->message,
            ] );
        $this->assertGuest();
    }

    public function test_delete_user()
    {
        $this->collection[ 0 ]->save();

        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/" . $this->collection[ 0 ][ "id" ]
        )->assertStatus( 200 )
            ->assertJsonStructure( [
                "status",
                "message",
            ] )->assertJson( [
                "status" => true,
            ] )
            ->assertJson( [
                "message" => $this->responseDeletedSuccess()->getData(
                )->message,
            ] )
        && $this->assertDatabaseMissing(
            User::getModel(),
            $this->collection[ 0 ]
        );
    }

    public function test_delete_user_unauthenticated()
    {
        $this->delete( $this->uri . "/" . $this->collection[ 0 ][ "id" ] )
            ->assertUnauthorized()
            ->assertJsonStructure( [
                "status",
                "message",
                "errors",
            ] )
            ->assertJson( [
                "status" => false,
            ] )
            ->assertJson( [
                "message" => $this->responseUnauthorized()->getData()->message,
            ] );
        $this->assertGuest();
    }

    public function test_delete_user_bad_id_minimum_length()
    {
        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/A"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "status",
            "message",
            "errors",
        ] );
    }

    public function test_delete_user_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/XXXXXXXX"
        )->assertJson( [
            'status' => false,
        ] )
            ->assertJson( [
                'message' => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }
}
