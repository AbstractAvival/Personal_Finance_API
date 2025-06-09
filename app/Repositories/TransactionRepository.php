<?php

namespace App\Repositories;

use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class TransactionRepository 
{
    public function create( array $data ): Model
    {
        if( $this->exists( $data[ "id" ] ) ) {
            throw new DuplicateRecordException();
        }

        if( !Category::where( "code", $data[ "category" ] )->exists() || !User::where( "id", $data[ "user_id" ] )->exists() ) {
            throw new DependencyConflictException();
        }

        return Transaction::create( [
            "amount" => $data[ "current_balance" ] ?? 0,
            "category" => $data[ "category" ],
            "description" => $data[ "description" ] ?? null,
            "date_of_transaction" => date_create()->format( "Y-m-d H:i:s" ),
            "id" => Transaction::max( "id" ) + 1,
            "type" => $data[ "type" ],
            "user_id" => $data[ "user_id" ],
        ] );
    }

    public function delete( string $transactionId ): bool
    {
        return ( bool )Transaction::where( "id", $transactionId )->delete();
    }

    public function exists( string $transactionId ): bool
    {
        return Transaction::where( "id", $transactionId )->exists();
    }

    public function get( 
        string $transactionId, 
        array $columns = [ "*" ]
    ): Model|null {
        return Transaction::when( !empty( $columns ), function ( $query ) use( $columns ) {
            return $query->select( $columns );
        } )
            ->where( "id", $transactionId )
            ->first();
    }

    public function list( 
        string $userId,
        array $columns = [ "*" ], 
        array $paginateParams = [] 
    ): LengthAwarePaginator {
        return Transaction::where( "user_id", $userId )
        ->orderBy( 
            $paginateParams[ "order_by" ] ?? "id", 
            $paginateParams[ "order" ] ?? "asc"
        )->paginate(
            $paginateParams[ "limit" ] ?? config( "pagination.default_limit" ),
            $columns,
            "page",
            $paginateParams[ "page" ] ?? 1
        );
    }

    public function update( array $data ): bool|Model
    {
        if( !$this->exists( $data[ "id" ] ) ) {
            throw new NotFoundException();
        }

        if( isset( $data[ "category" ] ) && !Category::where( "code", $data[ "category" ] )->exists() ) {
            throw new DependencyConflictException();
        }

        $oldData = $this->get( $data[ "id" ] );
        $update = tap( Transaction::where( "id", $data[ "id" ] ) )->update( [
            "amount" => $data[ "amount" ] ?? $oldData[ "amount" ],
            "category" => $data[ "category" ] ?? $oldData[ "category" ],
            "description" => $data[ "description" ] ?? $oldData[ "description" ],
            "type" => $data[ "type" ] ?? $oldData[ "type" ]
        ] )->first();

        return $update ?? false;
    }
}