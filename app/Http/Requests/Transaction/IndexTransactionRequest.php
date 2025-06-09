<?php

namespace App\Http\Requests\Transaction;

use App\Http\Requests\PaginateBaseRequest;

class IndexTransactionRequest extends PaginateBaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge( [
            "user_id" => $this->route( "userId" ),
        ] );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->base_rules, [
            "columns" => "array|nullable|in:amount,category,description,date_of_transaction,id,type",
            "order_by" => "string|nullable|in:id,first_name,last_name",
            "user_id" => "string|max:30|required",
        ] );
    }
}
