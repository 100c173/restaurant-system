<?php

namespace Modules\Restaurants\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RestaurantRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255|unique:restaurants,name',
            'description'     => 'nullable|string|max:1000',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'address'         => 'required|string|max:500',
            'phone'           => 'required|string|regex:/^\+?[0-9]{8,15}$/|unique:restaurants,phone',
            'email'           => 'required|email|max:255|unique:restaurants,email',
            'status'          => 'in:pending,approved,rejected',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_active'       => 'boolean',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'opening_time'    => 'required|date_format:H:i',
            'closing_time'    => 'required|date_format:H:i|after:opening_time',
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
