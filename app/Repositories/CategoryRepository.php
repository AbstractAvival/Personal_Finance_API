<?php

namespace App\Repositories;

use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository
{
    public function create( array $data ): Model
    {
        if( $this->exists( $data[ "code" ] ) ) {
            throw new DuplicateRecordException();
        }

        if( !User::where( "id", $data[ "user_id" ] )->exists() ) {
            throw new DependencyConflictException();
        }

        return Category::create( [
            "code" => $data[ "code" ],
            "name" => $data[ "name" ],
            "type" => $data[ "type" ],
            "user_id" => $data[ "user_id" ],
        ] );
    }

    public function delete( string $categoryCode ): bool
    {
        return ( bool )Category::where( "code", $categoryCode )->delete();
    }

    public function exists( string $categoryCode ): bool
    {
        return Category::where( "code", $categoryCode )->exists();
    }

    public function get( 
        string $categoryCode, 
        array $columns = [ "*" ]
    ): Model|null {
        return Category::when( !empty( $columns ), function ( $query ) use( $columns ) {
            return $query->select( $columns );
        } )
            ->where( "code", $categoryCode )
            ->first();
    }

    public function list( 
        string $userId,
        array $columns = [ "*" ], 
        array $paginateParams = [] 
    ): LengthAwarePaginator {
        return Category::where( "user_id", $userId )
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
        if( !$this->exists( $data[ "code" ] ) ) {
            throw new NotFoundException();
        }

        $oldData = $this->get( $data[ "code" ] );
        $update = tap( Category::where( "code", $data[ "code" ] ) )->update( [
            "name" => $data[ "amount" ] ?? $oldData[ "amount" ],
            "type" => $data[ "category" ] ?? $oldData[ "category" ],
        ] )->first();

        return $update ?? false;
    }
}