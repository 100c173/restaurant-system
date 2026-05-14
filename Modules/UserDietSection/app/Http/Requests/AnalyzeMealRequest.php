<?php

namespace Modules\UserDietSection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeMealRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:5120'], // 5MB
            'description' => 'nullable',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
