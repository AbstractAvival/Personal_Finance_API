<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            "current_balance" => "numeric|nullable",
            "email" => "email|nullable",
            "first_name" => "string|max:50|nullable",
            "language" => "string|max:10|nullable",
            "last_name" => "string|max:50|nullable",
            "password" => "string|max:200|nullable",
            "role" => "string|max:10|nullable",
        ];
    }
}
