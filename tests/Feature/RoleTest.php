<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private RoleCollection $collection;
    private string $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = $this->baseUri . "/roles";
    }

    public function test_category_exists(): void
    {
        $this->assertTrue( class_exists( Role::class ), "Category class has not been created." );
    }

    public function test_role_attributes(): void
    {
        $role = Role::factory()->create();
        $roleAttributes = $role->getAttributes(); 

        $this->assertArrayHasKey( "access_level", $roleAttributes, "access_level attribute was not found in role model" );        
        $this->assertArrayHasKey( "code", $roleAttributes, "code attribute was not found in role model" );
        $this->assertArrayHasKey( "name", $roleAttributes, "name attribute was not found in role model" );
    }

    public function test_role_controller_exists(): void
    {
        $this->assertTrue( class_exists( RoleController::class ), "RoleController class has not been created." );
    }

    public function test_role_controller_methods_exist(): void
    {
        $this->assertTrue( method_exists( RoleController::class, "delete" ), "The method 'delete' does not exist in the RoleController class" );
        $this->assertTrue( method_exists( RoleController::class, "index" ), "The method 'index' does not exist in the RoleController class" );
        $this->assertTrue( method_exists( RoleController::class, "show" ), "The method 'show' does not exist in the RoleController class" );
        $this->assertTrue( method_exists( RoleController::class, "store" ), "The method 'store' does not exist in the RoleController class" );
        $this->assertTrue( method_exists( RoleController::class, "update" ), "The method 'update' does not exist in the RoleController class" );
    }

    public function test_role_repository_exists(): void
    {
        $this->assertTrue( class_exists( RoleRepository::class ), "RoleRepository class has not been created." );
    }

    public function test_role_repository_methods_exist(): void
    {
        $this->assertTrue( method_exists( RoleRepository::class, "create" ), "The method 'create' does not exist in the RoleRepository class" );
        $this->assertTrue( method_exists( RoleRepository::class, "delete" ), "The method 'delete' does not exist in the RoleRepository class" );
        $this->assertTrue( method_exists( RoleRepository::class, "exists" ), "The method 'exists' does not exist in the RoleRepository class" );
        $this->assertTrue( method_exists( RoleRepository::class, "get" ), "The method 'get' does not exist in the RoleRepository class" );
        $this->assertTrue( method_exists( RoleRepository::class, "list" ), "The method 'list' does not exist in the RoleRepository class" );
        $this->assertTrue( method_exists( RoleRepository::class, "update" ), "The method 'update' does not exist in the RoleRepository class" );
    }

    public function test_get_roles_controller(): void
    {
        $collection = new RoleCollection(
            Role::factory()->new()
                ->count( 5 )
                ->create()
        );
        $userId = $collection[ 0 ]->getAttribute( "user_id" );
        $this->mock(
            RoleRepository::class,
            function ( MockInterface $mock ) use( $collection ) {
                $mock->shouldReceive( "list" )->with(
                    $collection[ 0 ]->getAttribute( "user_id" ),
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
            ->get( $this->uri . "?user_id=$userId" )
            ->assertOk()
            ->assertJson(
                $collection->response()->getData( true )
            ) && $this->assertAuthenticated();
    }

    public function test_get_roles_pagination_params_error(): void
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
        $this->assertCount( 4, $response->json()[ "errors" ] );
    }

    public function test_get_roles(): void
    {
        $this->collection = new RoleCollection(
            Role::factory()->new()
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
            ->assertJson( [
                $this->collection->response()->getData( true )
            ] );
    }

    public function test_get_roles_unauthenticated(): void
    {
        $response = $this->get( $this->uri . "?user_id=TEST" );
        $response->assertUnauthorized()->assertJsonStructure( [
            "message",
        ] )->assertJson( [
            "message" => $this->responseUnauthorized()->getData()->message,
        ] );
        $this->assertGuest();
    }

    public function test_get_role(): void
    {
        $this->collection = new RoleCollection(
            Role::factory()->new()
                ->count( 2 )
                ->create()
        );
        foreach( $this->collection as $record ) {
            $record->save();
        }
        $code = $this->collection[ 0 ]->getAttribute( "code" );
        $testData = $this->collection[ 0 ]->getAttributes();

        $response = $this->actingAs( $this->authenticatedUser )->get( 
            $this->uri . "/$code"
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

    public function test_get_role_code_too_long()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/JFDHJHFJDSFHDJKSFDSFJDSFKLDSJKL"
        )->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_get_role_invalid_code()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/JDLhs%&*@#!^72"
        )->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_get_role_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/NOTFOUND"
        )->assertJson( [
            "status" => false,
        ] )
            ->assertJson( [
                "message" => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }

    public function test_store_role(): void
    {
        $postData = [
                "access_level" => "Expense",
                "code" => "TESTCAT",
                "name" => "Test Category"
        ];
        $role = Role::factory()->create();
        $roleResource = new roleResource( $role );

        $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            $postData
        )->assertCreated()->assertJsonStructure( [
            "status",
            "message",
            "data"
        ] )->assertJson( [ "status" => true ] )
           ->assertJson( [
                "message" => $this->responseCreated( $roleResource )
                        ->getData()->message,
           ] );

        unset( $postData[ "user_id" ] );
        $this->assertDatabaseHas( Role::getModel(), $postData );
    }

    public function test_store_role_missing_access_level_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TEST",
                "name" => "Test role",
            ]
        );
        $response->assertInvalid( [
            "access_level",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_missing_code_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => 10,
                "name" => "Test role",
            ]
        );
        $response->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_missing_name_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => 10,
                "code" => "TEST",
            ]
        );
        $response->assertInvalid( [
            "name",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_access_level_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => 99999999999999,
                "code" => "TEST",
                "name" => "Test role",
            ]
        );
        $response->assertInvalid( [
            "access_level",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_code_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => 10,
                "code" => "TFSDADSADSADWQEST",
                "name" => "Test role",
            ]
        );
        $response->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_name_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => 10,
                "code" => "TEST",
                "name" => "Test role kdfhjskfhdjkshfdjsanfbjkdshafjkdasfjkfgyuewahjfkwebnhjfb",
            ]
        );
        $response->assertInvalid( [
            "name",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_access_level_parameter_invalid(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => "test",
                "code" => "TESTCAT",
                "name" => "Test Category",
            ]
        );
        $response->assertInvalid( [
            "access_level",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_code_parameter_invalid(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => "test",
                "code" => "fdhjT^&*#!3784#@#!2",
                "name" => "Test Category",
            ]
        );
        $response->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_code_parameter_invalid_2(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "access_level" => "test",
                "code" => 321678382513,
                "name" => "Test Category",
            ]
        );
        $response->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_role_unauthenticated(): void
    {
        $this->collection = new RoleCollection( 
            Role::factory()->new()
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

    public function test_delete_role()
    {
        $this->collection = new RoleCollection( 
            Role::factory()->new()
                ->count( 2 )
                ->create()
        );
        $this->collection[ 0 ]->save();
        $code = $this->collection[ 0 ]->getAttribute( "code" );

        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/$code"
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
            Role::getModel(),
            $this->collection[ 0 ]->getAttributes()
        );
    }

    public function test_delete_role_unauthenticated()
    {
        $this->collection = new RoleCollection( 
            Role::factory()->new()
                ->count( 2 )
                ->create()
        );
        $this->collection[ 0 ]->save();
        $code = $this->collection[ 0 ]->getAttribute( "code" );

        $this->delete( $this->uri . "/$code" )
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

    public function test_delete_role_invalid_code()
    {
        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/A$%^!@!hdjks321a;"
        )->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_delete_role_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/NOTFOUND"
        )->assertJson( [
            "status" => false,
        ] )
            ->assertJson( [
                "message" => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }

    public function test_update_role_invalid_id()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/Ahdjk$%@^!^&@!@s321a;"
        )->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_update_role_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/NOTFOUND"
        )->assertJson( [
            "status" => false,
        ] )
            ->assertJson( [
                "message" => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }
}
