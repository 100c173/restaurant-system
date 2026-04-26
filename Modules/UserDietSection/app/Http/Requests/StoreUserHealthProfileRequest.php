<?php

namespace Modules\UserDietSection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\UserDietSection\Models\UserHealthProfile;

class StoreUserHealthProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'gender' => ['required', 'in:Male,Female'],
            'birth_date' => ['required', 'date', 'before:today'],

            'height_cm' => ['required', 'numeric', 'min:50', 'max:300'],
            'weight_kg' => ['required', 'numeric', 'min:20', 'max:500'],

            'activity_level' => [
                'required',
            ],

            'goal' => ['required'],
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
