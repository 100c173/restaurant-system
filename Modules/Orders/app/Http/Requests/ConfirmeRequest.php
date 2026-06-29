<?php

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'type' => ['nullable', 'in:delivery,pickup'],
            'payment_method' => ['required', 'in:cash,shamCash'],
            'special_instructions' => ['nullable', 'string', 'max:500'],

            // Required only for delivery
            'delivery_address' => ['required', 'nullable', 'string'],

            // Required only for dine_in
            //'table_number' => ['required_if:type,dine_in', 'nullable', 'string'],
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('type', 'pickup'),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
