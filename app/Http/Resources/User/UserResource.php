<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray( Request $request ): array
    {
        return [
            "current_balance" => $this->whenHas( "current_balance" ),
            "email" => $this->whenHas( "email" ),
            "email_verified_at" => $this->whenHas( "email_verified_at" ),
            "first_name" => $this->whenHas( "first_name" ),
            "id" => $this->whenHas( "id" ),
            "language" => $this->whenHas( "language" ),
            "last_name" => $this->whenHas( "last_name" ),
            "last_login_date" => $this->whenHas( "last_login_date" ),
            "last_password_update" => $this->whenHas( "last_password_update" ),
            "password_expires_on" => $this->whenHas( "password_expires_on" ),
            "registration_date" => $this->whenHas( "registration_date" ),
            "role" => $this->whenHas( "role" ),
        ];
    }
}
