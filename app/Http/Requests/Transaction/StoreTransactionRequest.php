<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
        return [
            "amount" => "numeric|nullable",
            "category" => "string|max:10|required",
            "description" => "string|max:200|nullable",
            "type" => "string|max:30|in:Expense,Revenue|required",
            "user_id" => "string|max:30|required",
        ];
    }
}
