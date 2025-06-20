<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\PaginateBaseRequest;

class IndexRoleRequest extends PaginateBaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->base_rules, [
            "columns" => "array|nullable|in:code,name,type",
            "order_by" => "string|nullable|in:code,name",
        ] );
    }
}
