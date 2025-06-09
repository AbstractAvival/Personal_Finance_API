<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private TransactionCollection $collection;
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
    }

    public function test_get_transactions_controller(): void
    {
        $collection = new TransactionCollection(
            Transaction::factory()->new()
                ->count( 5 )
                ->create()
        );
        $this->mock(
            TransactionRepository::class,
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
        $this->assertCount( 3, $response->json()[ "errors" ] );
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

    public function test_get_transactions_unauthenticated(): void
    {
        $response = $this->get( $this->uri );
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

    public function test_get_transaction_bad_id_minimum_length()
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

    public function test_get_transaction_not_found()
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

    public function test_store_transaction(): void
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
        $transaction = Transaction::factory()->create();
        $transactionResource = new TransactionResource( $transaction );

        $this->actingAs( $this->authenticatedUser )->post(
            $this->uri,
            $postData
        )->assertCreated()->assertJsonStructure( [
            "status",
            "message",
            "data"
        ] )->assertJson( [ "status" => true ] )
           ->assertJson( [
                "message" => $this->responseCreated( $transactionResource )
                        ->getData()->message,
           ] );

        unset( $postData[ "password" ] );
        $this->assertDatabaseHas( Transaction::getModel(), $postData );
    }

    public function test_store_transaction_missing_id_parameter(): void
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

    public function test_store_transaction_id_minimum_length_fail(): void
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

    public function test_store_transaction_duplicate(): void
    {
        $transactionData = [
            // Add transaction data
        ];
        Transaction::factory()->create( $transactionData );
        
        $this->actingAs( $this->authenticatedUser )->post( 
            $this->uri,
            $transactionData
        )->assertJsonStructure( [
            "status",
            "message",
            "errors",
        ] )->assertConflict()
            ->assertJson( [
                "message" => $this->responseDuplicate()->getData()->message,
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

    public function test_delete_transaction_bad_id_minimum_length()
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

    public function test_delete_transaction_not_found()
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

    public function test_update_transaction_bad_id_minimum_length()
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

    public function test_update_transaction_not_found()
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
