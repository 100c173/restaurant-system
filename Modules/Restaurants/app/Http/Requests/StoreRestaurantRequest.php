<?php

namespace Modules\Restaurants\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRestaurantRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'owner_name' => 'required|string|max:255',

            'restaurant_name' => 'required|string|max:255',

            'owner_email' => 'required|string|email|max:255',

            'restaurant_email' => 'string|email|max:255',

            'owner_phone' => [
                'required',
                'string',
                'regex:/^\+963\s?9\d{2}\s?\d{3}\s?\d{3}$/',
            ],

            'restaurant_phone' => [
                'required',
                'string',
                'regex:/^\+963\s?9\d{2}\s?\d{3}\s?\d{3}$/',
            ],

            'address' => 'required|string|max:255',
        ];

    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => ' Data verification error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
