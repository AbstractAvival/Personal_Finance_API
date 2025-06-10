<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ShowUserRequest extends FormRequest
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
            "columns" => "array|nullable|in:current_balance,email,first_name,id,language,last_name,last_login_date,last_password_update,password_expires_on,registration_date",
            "id" => "string|alpha:ascii|max:30|required",
        ];
    }
}
