<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            "current_balance" => "numeric|nullable",
            "email" => "email|required",
            "first_name" => "string|max:50|required",
            "id" => "string|max:30|required",
            "language" => "string|max:10|nullable",
            "last_name" => "string|max:50|nullable",
            "password" => "string|max:200|required",
            "role" => "string|max:10|required",
        ];
    }
}
