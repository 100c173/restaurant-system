<?php

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'payment_method' => ['required', 'in:cash,online'],
            'special_instructions' => ['nullable', 'string', 'max:500'],

            // Required only for delivery
            'delivery_address' => ['required_if:type,delivery', 'nullable', 'string'],
            'delivery_lat' => ['required_if:type,delivery', 'nullable', 'numeric'],
            'delivery_lng' => ['required_if:type,delivery', 'nullable', 'numeric'],
            'delivery_fee' => ['required_if:type,delivery', 'nullable', 'numeric', 'min:0'],

            // Required only for dine_in
            'table_number' => ['required_if:type,dine_in', 'nullable', 'string'],
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
