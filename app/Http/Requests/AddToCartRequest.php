<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddToCartRequest extends FormRequest
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
            'restaurant_id' => ['required', 'string', 'exists:restaurants,id'],
            'item_id'       => ['required', 'integer', 'min:1'],
            'variant_id'    => ['required','integer','min:1'],
            'quantity'      => ['sometimes', 'integer', 'min:1', 'max:99'],
            'description'   => ['sometimes' , 'string'],
            'modifiers_id'  => ['array'],
        ];
    }
    public function messages(): array
    {
        return [
            'restaurant_id.exists' => 'The selected restaurant does not exist or is inactive.',
            'item_id.min'          => 'Invalid item selected.',
            'variant_id.min'       => 'Invalid variant item selected.',
            'quantity.max'         => 'You cannot add more than 99 of the same item.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
