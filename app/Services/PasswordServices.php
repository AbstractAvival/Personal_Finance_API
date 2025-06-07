<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Hash;

class PasswordServices
{
    public function generateSalt()
    {
        return base64_encode( random_bytes( config( "security.default_salt_byte_length" ) ) );
    }

    public function generateSecurePasswordData( 
        string $encryptedPassword, 
        ?string $salt = null 
    ): array {
        $secureData = [
            "hashed_password" => "",
            "salt" => $salt ?? $this->generateSalt(),
            "status" => false,
        ];

        try {
            $password = Crypt::decryptString( $encryptedPassword );
            $saltedHash = Hash::make( $secureData[ "salt" ] . $password );
            $secureData[ "hashed_password" ] = Hash::make( $saltedHash . file_get_contents( getenv( "PEPPER" ) ) );
            $secureData[ "status" ] = true;
            return $secureData;
        } catch( DecryptException $decryptException ) {
            return $secureData;
        }
    }
}