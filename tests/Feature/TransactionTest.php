<?php

namespace Tests\Feature;

use App\Http\Controllers\TransactionController;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Transaction\TransactionCollection;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\User\UserCollection;
use App\Models\Category;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private CategoryCollection $categoryCollection;
    private TransactionCollection $collection;
    private UserCollection $userCollection;
    private string $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = $this->baseUri . "/transactions";
    }

    public function test_transaction_exists(): void
    {
        $this->assertTrue( class_exists( Transaction::class ), "Transaction class has not been created." );
    }

    public function test_transaction_attributes(): void
    {
        $transaction = Transaction::factory()->create();
        $transactionAttributes = $transaction->getAttributes(); 

        $this->assertArrayHasKey( "amount", $transactionAttributes, "amount attribute was not found in user model" );
        $this->assertArrayHasKey( "category", $transactionAttributes, "category attribute was not found in user model" );
        $this->assertArrayHasKey( "description", $transactionAttributes, "description attribute was not found in user model" );        
        $this->assertArrayHasKey( "date_of_transaction", $transactionAttributes, "date_of_transaction attribute was not found in user model" );
        $this->assertArrayHasKey( "id", $transactionAttributes, "id attribute was not found in user model" );
        $this->assertArrayHasKey( "type", $transactionAttributes, "type attribute was not found in user model" );
        $this->assertArrayHasKey( "user_id", $transactionAttributes, "user_id attribute was not found in user model" );
    }

    public function test_transaction_controller_exists(): void
    {
        $this->assertTrue( class_exists( TransactionController::class ), "TransactionController class has not been created." );
    }

    public function test_transaction_controller_methods_exist(): void
    {
        $this->assertTrue( method_exists( TransactionController::class, "delete" ), "The method 'delete' does not exist in the TransactionController class" );
        $this->assertTrue( method_exists( TransactionController::class, "index" ), "The method 'index' does not exist in the TransactionController class" );
        $this->assertTrue( method_exists( TransactionController::class, "show" ), "The method 'show' does not exist in the TransactionController class" );
        $this->assertTrue( method_exists( TransactionController::class, "store" ), "The method 'store' does not exist in the TransactionController class" );
        $this->assertTrue( method_exists( TransactionController::class, "update" ), "The method 'update' does not exist in the TransactionController class" );
    }

    public function test_transaction_repository_exists(): void
    {
        $this->assertTrue( class_exists( TransactionRepository::class ), "TransactionRepository class has not been created." );
    }

    public function test_transaction_repository_methods_exist(): void
    {
        $this->assertTrue( method_exists( TransactionRepository::class, "create" ), "The method 'create' does not exist in the TransactionRepository class" );
        $this->assertTrue( method_exists( TransactionRepository::class, "delete" ), "The method 'delete' does not exist in the TransactionRepository class" );
        $this->assertTrue( method_exists( TransactionRepository::class, "exists" ), "The method 'exists' does not exist in the TransactionRepository class" );
        $this->assertTrue( method_exists( TransactionRepository::class, "get" ), "The method 'get' does not exist in the TransactionRepository class" );
        $this->assertTrue( method_exists( TransactionRepository::class, "list" ), "The method 'list' does not exist in the TransactionRepository class" );
        $this->assertTrue( method_exists( TransactionRepository::class, "update" ), "The method 'update' does not exist in the TransactionRepository class" );
    }

    public function test_get_transactions_controller(): void
    {
        $collection = new TransactionCollection(
            Transaction::factory()->new()
                ->count( 5 )
                ->create()
        );
        $userId = $collection[ 0 ]->getAttribute( "user_id" );
        $this->mock(
            TransactionRepository::class,
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

    public function test_get_transactions_pagination_params_error(): void
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

    public function test_get_transactions(): void
    {
        $this->collection = new TransactionCollection(
            Transaction::factory()->new()
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

    public function test_get_transactions_unauthenticated(): void
    {
        $response = $this->get( $this->uri . "?user_id=TEST" );
        $response->assertUnauthorized()->assertJsonStructure( [
            "message",
        ] )->assertJson( [
            "message" => $this->responseUnauthorized()->getData()->message,
        ] );
        $this->assertGuest();
    }

    public function test_get_transaction(): void
    {
        $this->collection = new TransactionCollection(
            Transaction::factory()->new()
                ->count( 2 )
                ->create()
        );
        foreach( $this->collection as $record ) {
            $record->save();
        }
        $id = $this->collection[ 0 ]->getAttribute( "id" );
        $testData = $this->collection[ 0 ]->getAttributes();
        unset( $testData[ "user_id" ] );

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

    public function test_get_transaction_invalid_id()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/Adfjklsa;"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_get_transaction_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->get(
            $this->uri . "/9999999999"
        )->assertJson( [
            "status" => false,
        ] )
            ->assertJson( [
                "message" => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }

    public function test_store_transaction(): void
    {
        $this->categoryCollection = new CategoryCollection(
            Category::factory()->new()
                ->count( 2 )
                ->create()
        );
        foreach( $this->categoryCollection as $record ) {
            $record->save();
        }

        $postData = [
                "amount" => 2644.47,
                "category" => $this->categoryCollection[ 0 ]->getAttribute( "code" ),
                "description" => "This is a test description.",
                "type" => "Expense",
                "user_id" => $this->categoryCollection[ 0 ]->getAttribute( "user_id" ),
        ];
        $transaction = Transaction::factory()->create();
        $transactionResource = new TransactionResource( $transaction );

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
                "message" => $this->responseCreated( $transactionResource )
                        ->getData()->message,
           ] );

        $postData[ "id" ] = $response->json()[ "data" ][ "id" ];
        $this->assertDatabaseHas( Transaction::getModel(), $postData );
    }

    public function test_store_transaction_missing_category_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "amount" => 2644.47,
                "description" => "This is a test description.",
                "type" => "Expense",
                "user_id" => "TEST",
            ]
        );
        $response->assertInvalid( [
            "category",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_transaction_missing_type_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "amount" => 2644.47,
                "category" => "TEST",
                "description" => "This is a test description.",
                "user_id" => "Happy",
            ]
        );
        $response->assertInvalid( [
            "type",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_transaction_missing_user_id_parameter(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "amount" => 2644.47,
                "category" => "TEST",
                "description" => "This is a test description.",
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

    public function test_store_transaction_category_parameter_too_long(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "amount" => 2644.47,
                "category" => "HJDFSHFDSJKFHDSFDSFJDSHJKFDS",
                "description" => "This is a test description.",
                "type" => "Expense",
                "user_id" => "Happy",
            ]
        );
        $response->assertInvalid( [
            "category",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_store_transaction_type_parameter_invalid(): void
    {
        $response = $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            [
                "amount" => 2644.47,
                "category" => "TEST",
                "description" => "This is a test description.",
                "type" => "TEST",
                "user_id" => "Happy",
            ]
        );
        $response->assertInvalid( [
            "type",
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

    public function test_store_transaction_unauthenticated(): void
    {
        $this->collection = new TransactionCollection( 
            Transaction::factory()->new()
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

    public function test_delete_transaction()
    {
        $this->collection = new TransactionCollection( 
            Transaction::factory()->new()
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
            Transaction::getModel(),
            $this->collection[ 0 ]->getAttributes()
        );
    }

    public function test_delete_transaction_unauthenticated()
    {
        $this->collection = new TransactionCollection( 
            Transaction::factory()->new()
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

    public function test_delete_user_invalid_id()
    {
        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/Ahdjks321a;"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_delete_transaction_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->delete(
            $this->uri . "/9999999999"
        )->assertJson( [
            "status" => false,
        ] )
            ->assertJson( [
                "message" => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }

    public function test_update_user_invalid_id()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/Ahdjks321a;"
        )->assertInvalid( [
            "id",
        ] )->assertJsonStructure( [
            "message",
            "errors",
        ] );
    }

    public function test_update_transaction_not_found()
    {
        $this->actingAs( $this->authenticatedUser )->patch(
            $this->uri . "/9999999999"
        )->assertJson( [
            "status" => false,
        ] )
            ->assertJson( [
                "message" => $this->responseNotFound()->getData()->message,
            ] )->assertStatus( $this->responseNotFound()->status() );
    }
}
