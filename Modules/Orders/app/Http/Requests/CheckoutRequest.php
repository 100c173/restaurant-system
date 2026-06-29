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
            'payment_code' => [
                'nullable',
                'numeric',
                'required_without:invoice',
            ],

            'tenant_id' => [
                'required',
                'string',
            ],
            'reference_number' => [
                'required',
                'string',
            ],
            'invoice' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'required_without:payment_code',
                'max:2048'
            ],
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
