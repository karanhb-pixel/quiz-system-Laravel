<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Category;

class StoreCategoryRequest extends FormRequest
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
            'name' =>[
                'required',
                'string', 
                'max:255',
                function ($attribute, $value, $fail) {
                $slug = str($value)->slug();
                if (Category::where('slug', $slug)->exists()) {
                    $fail('A category with a similar name already exists.');
                }
            },
            ],
            'description' => [
                'nullable', 
                'string'
            ],
        ];
    }
}
