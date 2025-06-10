<?php

use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Exceptions\NotFoundException;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class RoleRepository
{
    public function create( array $data ): Model
    {
        if( $this->exists( $data[ "code" ] ) ) {
            throw new DuplicateRecordException();
        }

        return Role::create( [
            "access_level" => $data[ "access_level" ],
            "code" => $data[ "code" ],
            "name" => $data[ "name" ],
        ] );
    }

    public function delete( string $roleCode ): bool
    {
        return ( bool )Role::where( "code", $roleCode )->delete();
    }

    public function exists( string $roleCode ): bool
    {
        return Role::where( "code", $roleCode )->exists();
    }

    public function get( 
        string $roleCode, 
        array $columns = [ "*" ]
    ): Model|null {
        return Role::when( !empty( $columns ), function ( $query ) use( $columns ) {
            return $query->select( $columns );
        } )
            ->where( "code", $roleCode )
            ->first();
    }

    public function list( 
        array $columns = [ "*" ], 
        array $paginateParams = [] 
    ): LengthAwarePaginator {
        return Role::orderBy( 
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
        $update = tap( Role::where( "code", $data[ "code" ] ) )->update( [
            "access_level" => $data[ "access_level" ] ?? $oldData[ "access_level" ],
            "name" => $data[ "amount" ] ?? $oldData[ "amount" ],
        ] )->first();

        return $update ?? false;
    }
}