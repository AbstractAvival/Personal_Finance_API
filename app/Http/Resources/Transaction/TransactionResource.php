<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray( Request $request ): array
    {
        return [
            "amount" => $this->whenHas( "amount" ),
            "category" => $this->whenHas( "category" ),
            "description" => $this->whenHas( "description" ),
            "date_of_transaction" => $this->whenHas( "date_of_transaction" ),
            "id" => $this->whenHas( "id" ),
            "type" => $this->whenHas( "type" ),
        ];
    }
}
