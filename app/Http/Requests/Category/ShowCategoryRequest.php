<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class ShowCategoryRequest extends FormRequest
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
            "code" => $this->route( "code" ),
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
            "code" => "string|alpha:ascii|max:10|required",
            "columns" => "array|nullable|in:code,name,type",
        ];
    }
}
