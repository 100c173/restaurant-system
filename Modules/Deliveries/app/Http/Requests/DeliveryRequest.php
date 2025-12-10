<?php

namespace Modules\Deliveries\Http\Requests;

use App\Rules\EmailVerifiedRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeliveryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email'            => ['required' , 'string' ,'exists:users,email' , new EmailVerifiedRule() ],
            'password'         => ['required','string','min:8'],   
            'name'             => ['required', 'string', 'max:255'],
            'phone_number'     => ['required', 'string', 'regex:/^09[0-9]{8}$/'],
            'national_id'      => ['required', 'string', 'max:20', 'unique:delivery_requests,national_id'],
            
            'vehicle_type'     => ['required'],
            'vehicle_number'   => ['required', 'string', 'max:50'],
            'experience_note'  => ['nullable', 'string', 'max:1000'],


            'personal_photo'   => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'id_card_front'    => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'id_card_back'     => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'driving_license'  => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
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
