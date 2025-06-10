<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            "id" => $this->route( "id" ),
        ] );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "amount" => "numeric|nullable",
            "category" => "string|alpha:ascii|max:10|nullable",
            "description" => "string|max:200|nullable",
            "id" => "numeric|max_digits:10|required",
            "type" => "string|max:30|in:Expense,Revenue|nullable",
        ];
    }
}
