<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray( Request $request ): array
    {
        return [
            "code" => $this->whenHas( "code" ),
            "name" => $this->whenHas( "name" ),
            "type" => $this->whenHas( "type" ),
            "user_id" => $this->whenHas( "user_id" ),
        ];
    }
}
