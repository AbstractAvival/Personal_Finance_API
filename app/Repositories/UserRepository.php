<?php

namespace App\Repositories;

use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Exceptions\NotFoundException;
use App\Exceptions\PasswordDecryptionException;
use App\Models\User;
use App\Services\PasswordServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    public function __construct(
        private readonly PasswordServices $passwordServices
    ) {
    }

    public function create( array $data ): Model
    {
        if( $this->exists( $data[ "id" ] ) ) {
            throw new DuplicateRecordException();
        }

        $securePasswordData = $this->passwordServices->generateSecurePasswordData( $data[ "password" ] );
        $passwordExpirationOffset = config( "security.default_password_expiration_day_offset" );
        if( !$securePasswordData[ "status" ] ) {
            throw new PasswordDecryptionException();
        }

        return User::create( [
            "current_balance" => $data[ "current_balance" ] ?? 0,
            "email" => $data[ "email" ],
            "first_name" => $data[ "first_name" ],
            "id" => $data[ "id" ],
            "language" => $data[ "language" ] ?? config( "language.default_language" ),
            "last_name" => $data[ "last_name" ] ?? null,
            "last_login_date" => null,
            "last_password_update" => now(),
            "password" => $securePasswordData[ "hashed_password" ],
            "password_expires_on" => date( "Y-m-d H:i:s", strtotime( "+{ $passwordExpirationOffset } days" ) ),
            "registration_date" => now(),
            "role" => $data[ "role" ] ?? "nml_usr",
            "salt" => $securePasswordData[ "salt" ],
        ] );
    }

    public function delete( string $userId ): bool
    {
        //Check if user has any transactions before deleting
        return ( bool )User::where( "id", $userId )->delete();
    }

    public function exists( string $userId ): bool
    {
        return User::where( "id", $userId )->exists();
    }

    public function get( 
        string $userId, 
        array $columns = [ "*" ]
    ): Model|null {
        return User::when( !empty( $columns ), function ( $query ) use( $columns ) {
            return $query->select( $columns );
        } )
            ->where( "id", $userId )
            ->first();
    }

    public function list( 
        array $columns = [ "*" ], 
        array $paginateParams = [] 
    ): LengthAwarePaginator {
        return User::orderBy( 
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

        $oldData = $this->get( $data[ "id" ] );
        if( isset( $data[ "password" ] ) ) {
            $securePasswordData = $this->passwordServices->generateSecurePasswordData( $data[ "password" ], $oldData[ "salt" ] );
            $passwordExpirationOffset = config( "security.default_password_expiration_day_offset" );
            if( !$securePasswordData[ "status" ] ) {
                throw new PasswordDecryptionException();
            }
        }

        $update = tap( User::where( "id", $data[ "id" ] ) )->update( [
            "current_balance" => $data[ "current_balance" ] ?? $oldData->current_balance,
            "email" => $data[ "email" ] ?? $oldData->email,
            "first_name" => $data[ "first_name" ] ?? $oldData->first_name,
            "language" => $data[ "language" ] ?? $oldData->language,
            "last_name" => $data[ "last_name" ] ?? $oldData->last_name,
            "last_login_date" => $data[ "last_login_date" ] ?? $oldData->last_login_date,
            "last_password_update" => $data[ "last_password_update" ] ?? $oldData->last_password_update,
            "password" => isset( $data[ "password" ] ) ? $securePasswordData[ "hashed_password" ] : $oldData->password,
            "password_expires_on" => isset( $data[ "password" ] ) ? date( "Y-m-d H:i:s", strtotime( "+{ $passwordExpirationOffset } days" ) ) : $oldData->password_expires_on,
            "role" => $data[ "role" ] ?? $oldData->role,
        ] )->first();

        return $update ?? false;
    }
}