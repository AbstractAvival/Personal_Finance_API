<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray( Request $request ): array
    {
        return [
            "name" => $this->whenHas( "name" ),
            "email" => $this->whenHas( "email" ),
            "email_verified_at" => $this->whenHas( "email_verified_at" ),
            "remember_token" => $this->whenHas( "remember_token" ),
        ];
    }
}
