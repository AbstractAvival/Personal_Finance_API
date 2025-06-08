<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class PaginateBaseRequest extends FormRequest
{
    protected array $base_rules
        = [
            "page" => "integer|min:1",
            "order" => "string|in:asc,desc",
            "limit" => "integer|min:1",
            "order_by" => "string",
        ];

    public static function getPaginateParams( array $request = [] ): array
    {
        if( empty( $request ) ) {
            $request = request()->all();
        }
        return [
            "page" => $request[ "page" ] ?? 1,
            "limit" => $request[ "limit" ] ?? config( "pagination.default_limit" ),
            "order" => $request[ "order" ] ?? "asc",
            "order_by" => $request[ "order_by" ] ?? "",
        ];
    }
}
