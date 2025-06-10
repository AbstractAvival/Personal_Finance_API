<?php

namespace Tests\Feature;

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private CategoryCollection $collection;
    private string $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = $this->baseUri . "/categories";
    }

    public function test_category_exists(): void
    {
        $this->assertTrue( class_exists( Category::class ), "Category class has not been created." );
    }

    public function test_category_attributes(): void
    {
        $category = Category::factory()->create();
        $categoryAttributes = $category->getAttributes(); 

        $this->assertArrayHasKey( "code", $categoryAttributes, "code attribute was not found in user model" );
        $this->assertArrayHasKey( "name", $categoryAttributes, "name attribute was not found in user model" );
        $this->assertArrayHasKey( "type", $categoryAttributes, "type attribute was not found in user model" );        
        $this->assertArrayHasKey( "user_id", $categoryAttributes, "user_id attribute was not found in user model" );
    }

    public function test_category_controller_exists(): void
    {
        $this->assertTrue( class_exists( CategoryController::class ), "CategoryController class has not been created." );
    }

    public function test_category_controller_methods_exist(): void
    {
        $this->assertTrue( method_exists( CategoryController::class, "delete" ), "The method 'delete' does not exist in the CategoryController class" );
        $this->assertTrue( method_exists( CategoryController::class, "index" ), "The method 'index' does not exist in the CategoryController class" );
        $this->assertTrue( method_exists( CategoryController::class, "show" ), "The method 'show' does not exist in the CategoryController class" );
        $this->assertTrue( method_exists( CategoryController::class, "store" ), "The method 'store' does not exist in the CategoryController class" );
        $this->assertTrue( method_exists( CategoryController::class, "update" ), "The method 'update' does not exist in the CategoryController class" );
    }

    public function test_category_repository_exists(): void
    {
        $this->assertTrue( class_exists( CategoryRepository::class ), "CategoryRepository class has not been created." );
    }

    public function test_category_repository_methods_exist(): void
    {
        $this->assertTrue( method_exists( CategoryRepository::class, "create" ), "The method 'create' does not exist in the CategoryRepository class" );
        $this->assertTrue( method_exists( CategoryRepository::class, "delete" ), "The method 'delete' does not exist in the CategoryRepository class" );
        $this->assertTrue( method_exists( CategoryRepository::class, "exists" ), "The method 'exists' does not exist in the CategoryRepository class" );
        $this->assertTrue( method_exists( CategoryRepository::class, "get" ), "The method 'get' does not exist in the CategoryRepository class" );
        $this->assertTrue( method_exists( CategoryRepository::class, "list" ), "The method 'list' does not exist in the CategoryRepository class" );
        $this->assertTrue( method_exists( CategoryRepository::class, "update" ), "The method 'update' does not exist in the CategoryRepository class" );
    }

    public function test_get_categories_controller(): void
    {
        $collection = new CategoryController(
            Category::factory()->new()
                ->count( 5 )
                ->create()
        );
        $userId = $collection[ 0 ]->getAttribute( "user_id" );
        $this->mock(
            CategoryRepository::class,
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

    public function test_get_categories_pagination_params_error(): void
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

    public function test_get_categories(): void
    {
        $this->collection = new CategoryCollection(
            Category::factory()->new()
                ->count( 5 )
                ->create()
        );
        foreach( $this->collection as $record ) {
            $record->save();
        }
        $userId = $this->collection[ 0 ]->getAttribute( "user_id" );
        $testData = $this->collection[ 0 ]->getAttributes();
        unset( $testData[ "user_id" ] );

        $response = $this->actingAs( $this->authenticatedUser )->get( $this->uri . "?user_id=$userId" );
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
                "data" => [
                    $testData
                ]
            ] );
    }

    public function test_get_categories_unauthenticated(): void
    {
        $response = $this->get( $this->uri . "?user_id=TEST" );
        $response->assertUnauthorized()->assertJsonStructure( [
            "message",
        ] )->assertJson( [
            "message" => $this->responseUnauthorized()->getData()->message,
        ] );
        $this->assertGuest();
    }

    public function test_get_category(): void
    {
        $this->collection = new CategoryCollection(
            Category::factory()->new()
                ->count( 2 )
                ->create()
        );
        foreach( $this->collection as $record ) {
            $record->save();
        }
        $code = $this->collection[ 0 ]->getAttribute( "code" );
        $testData = $this->collection[ 0 ]->getAttributes();
        unset( $testData[ "user_id" ] );

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

    public function test_get_category_invalid_code()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/Ad#!#!fjklsa;"
        )->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_get_category_not_found()
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

    public function test_store_category(): void
    {
        $this->userCollection = new UserCollection(
            User::factory()->new()
                ->count( 2 )
                ->create()
        );
        foreach( $this->userCollection as $record ) {
            $record->save();
        }

        $postData = [
                "code" => "TESTCAT",
                "name" => "Test Category",
                "type" => "Expense",
                "user_id" => $this->userCollection[ 0 ]->getAttribute( "id" ),
        ];
        $category = Category::factory()->create();
        $categoryResource = new CategoryResource( $category );

        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            $postData
        );
        
        $response->assertCreated()->assertJsonStructure( [
            "status",
            "message",
            "data"
        ] )->assertJson( [ "status" => true ] )
           ->assertJson( [
                "message" => $this->responseCreated( $categoryResource )
                        ->getData()->message,
           ] );

        unset( $postData[ "user_id" ] );
        $this->assertDatabaseHas( Category::getModel(), $postData );
    }

    public function test_store_category_missing_code_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "name" => "Test Category",
                "type" => "Expense",
                "user_id" => "TEST",
            ]
        );
        $response->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_missing_name_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCAT",
                "type" => "Expense",
                "user_id" => "TEST",
            ]
        );
        $response->assertInvalid( [
            "name",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_missing_type_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCAT",
                "name" => "Test Category",
                "user_id" => "TEST",
            ]
        );
        $response->assertInvalid( [
            "type",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_missing_user_id_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCAT",
                "name" => "Test Category",
                "type" => "Expense",
            ]
        );
        $response->assertInvalid( [
            "user_id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_code_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCATDFFASDFASDFDASFDSAFDSA",
                "name" => "Test Category",
                "type" => "Expense",
                "user_id" => "TEST"
            ]
        );
        $response->assertInvalid( [
            "code",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_name_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCAT",
                "name" => "Test Category daf234dfssafdsaf1341asdasdsaf4btreewrr4",
                "type" => "Expense",
                "user_id" => "TEST"
            ]
        );
        $response->assertInvalid( [
            "name",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_user_id_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCAT",
                "name" => "Test Category",
                "type" => "Expense",
                "user_id" => "TESTDFSF32FDSSGSVHNTRU45DFASFVERHYTJYGRF4FDDSADSAWBFEWAFV235"
            ]
        );
        $response->assertInvalid( [
            "user_id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_transaction_user_id_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "amount" => 2644.47,
                "category" => "TEST",
                "description" => "This is a test description.",
                "type" => "Expense",
                "user_id" => "JFDSHFDSJKFBDSHJAFGYDUAFBWEJKFNHGYUCXABFJKDSBAFUHEVWUHAF"
            ]
        );
        $response->assertInvalid( [
            "user_id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_type_parameter_invalid(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "code" => "TESTCAT",
                "name" => "Test Category",
                "type" => "test",
                "user_id" => "TEST"
            ]
        );
        $response->assertInvalid( [
            "type",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_category_unauthenticated(): void
    {
        $this->collection = new CategoryCollection( 
            Category::factory()->new()
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

    public function test_delete_category()
    {
        $this->collection = new CategoryCollection( 
            Category::factory()->new()
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
            Category::getModel(),
            $this->collection[ 0 ]->getAttributes()
        );
    }

    public function test_delete_category_unauthenticated()
    {
        $this->collection = new CategoryCollection( 
            Category::factory()->new()
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

    public function test_delete_category_invalid_id()
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

    public function test_delete_category_not_found()
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

    public function test_update_category_invalid_id()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/Ahdjk$%@^!^&@!@s321a;"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_update_category_not_found()
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
