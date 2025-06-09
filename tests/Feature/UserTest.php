<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Mockery\MockInterface;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertTrue( method_exists( UserController::class, "update" ), "The method 'update' does not exist in the UserController class" );
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
        $this->assertTrue( method_exists( UserRepository::class, "update" ), "The method 'update' does not exist in the UserRepository class" );
    }

    public function test_get_users_controller(): void
    {
        $collection = new UserCollection(
            User::factory()->new()
                ->count( 5 )
                ->create()
        );
        $this->mock(
            UserRepository::class,
            function ( MockInterface $mock ) use( $collection ) {
                $mock->shouldReceive( "list" )->with(
                    [ "*" ],
                    [
                        "page" => 1,
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
        ] )->assertJsonStructure( [
            "message", 
            "errors"
        ] );

        $this->assertAuthenticated();
        $this->assertCount( 3, $response->json()[ "errors" ] );
    }

    public function test_get_users(): void
    {
        $this->collection = new UserCollection(
            User::factory()->new()
                ->count( 5 )
                ->create()
        );
        foreach( $this->collection as $record ) {
            $record->save();
        }

        $response = $this->actingAs( $this->authenticatedUser )->get( $this->uri );
        $this->assertAuthenticated();

        $response
            ->assertOk()->assertJsonStructure( [
                "status",
                "data",
                "meta",
            ] )->assertJson( [
                "status" => true,
            ] )
            ->assertJson(
                $this->collection->response()->getData( true )
            );
    }

    public function test_get_users_unauthenticated(): void
    {
        $response = $this->get( $this->uri );
        $response->assertUnauthorized()->assertJsonStructure( [
            "message",
        ] )->assertJson( [
            "message" => $this->responseUnauthorized()->getData()->message,
        ] );
        $this->assertGuest();
    }

    public function test_get_user(): void
    {
        $this->collection = new UserCollection(
            User::factory()->new()
                ->count( 2 )
                ->create()
        );
        foreach( $this->collection as $record ) {
            $record->save();
        }
        $id = $this->collection[ 0 ]->getAttribute( "id" );
        $testData = $this->collection[ 0 ]->getAttributes();
        unset( 
            $testData[ "email_verified_at" ], 
            $testData[ "password" ],
            $testData[ "remember_token" ],
            $testData[ "salt" ] 
        );

        $response = $this->actingAs( $this->authenticatedUser )->get( 
            $this->uri . "/$id"
        );
        $this->assertAuthenticated();

        $response->assertOk()->assertJsonStructure( [
                "status",
                "data"
            ] )->assertJson( [
                "status" => true,
            ] )
            ->assertJson( [
                "data" => $testData
            ] );
    }

    public function test_get_user_bad_id_minimum_length()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/A"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_get_user_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/XXXXXXXX"
        )->assertJson( [
            'status' => false,
        ] )
            ->assertJson( [
                'message' => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }

    public function test_store_user(): void
    {
        $postData = [
                "current_balance" => 2604944.47,
                "email" => "ydickinson@example.com",
                "first_name" => "Justyn",
                "id" => "WWUFSKGX",
                "language" => "en-us",
                "last_name" => "O'Kon",
                "password" => Crypt::encryptString( "123456789" ),
                "role" => "nml_usr",
        ];
        $user = User::factory()->create();
        $userResource = new UserResource( $user );

        $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            $postData
        )->assertCreated()->assertJsonStructure( [
            "status",
            "message",
            "data"
        ] )->assertJson( [ "status" => true ] )
           ->assertJson( [
                "message" => $this->responseCreated( $userResource )
                        ->getData()->message,
           ] );

        unset( $postData[ "password" ] );
        $this->assertDatabaseHas( User::getModel(), $postData );
    }

    public function test_store_user_missing_id_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "current_balance" => 2644.47,
                "email" => "ydion@example.com",
                "first_name" => "Justyn",
                "language" => "en-us",
                "last_name" => "Happy",
                "password" => Crypt::encryptString( "123456789" ),
                "role" => "nml_usr",
            ]
        );
        $response->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_user_id_minimum_length_fail(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "current_balance" => 2644.47,
                "email" => "ydion@example.com",
                "first_name" => "Justyn",
                "id" => "TE",
                "language" => "en-us",
                "last_name" => "Happy",
                "password" => Crypt::encryptString( "123456789" ),
                "role" => "nml_usr",
            ]
        );
        $response->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_user_duplicate(): void
    {
        $userData = [
            "current_balance" => 2604944.47,
            "email" => "ydickinson@example.com",
            "first_name" => "Justyn",
            "id" => "WWUFSKGX",
            "language" => "en-us",
            "last_name" => "O'Kon",
            "password" => Crypt::encryptString( "123456789" ),
            "role" => "nml_usr",
        ];
        User::factory()->create( $userData );
        
        $this->actingAs( $this->authenticatedUser )->post( 
            $this->uri,
            $userData
        )->assertJsonStructure( [
            "status",
            "message",
            "errors",
        ] )->assertConflict()
            ->assertJson( [
                "message" => $this->responseDuplicate()->getData()->message,
            ] );
    }

    public function test_store_user_unauthenticated(): void
    {
        $this->collection = new UserCollection( 
            User::factory()->new()
                ->count( 2 )
                ->create()
        );

        $response = $this->post( $this->uri, $this->collection[ 0 ]->toArray() );
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
        $this->collection = new UserCollection( 
            User::factory()->new()
                ->count( 2 )
                ->create()
        );
        $this->collection[ 0 ]->save();
        $id = $this->collection[ 0 ]->getAttribute( "id" );

        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/$id"
        )->assertStatus( 200 )
            ->assertJsonStructure( [
                "status",
                "message",
            ] )->assertJson( [
                "status" => true,
            ] )
            ->assertJson( [
                "message" => $this->responseDeletedSuccess()->getData()->message,
            ] )
        && $this->assertDatabaseMissing(
            User::getModel(),
            $this->collection[ 0 ]->getAttributes()
        );
    }

    public function test_delete_user_unauthenticated()
    {
        $this->collection = new UserCollection( 
            User::factory()->new()
                ->count( 2 )
                ->create()
        );
        $this->collection[ 0 ]->save();
        $id = $this->collection[ 0 ]->getAttribute( "id" );

        $this->delete( $this->uri . "/$id" )
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

    public function test_update_user_bad_id_minimum_length()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/A"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_update_user_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/XXXXXXXX"
        )->assertJson( [
            'status' => false,
        ] )
            ->assertJson( [
                'message' => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }
}
