<?php

namespace App\Http\Requests\User;

use App\Http\Requests\PaginateBaseRequest;

class IndexUserRequest extends PaginateBaseRequest
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
            "columns" => "array|nullable|in:current_balance,email,first_name,id,language,last_name,last_login_date,last_password_update,password_expires_on,registration_date",
            "order_by" => "string|nullable|in:id,first_name,last_name",
        ] );
    }
}
