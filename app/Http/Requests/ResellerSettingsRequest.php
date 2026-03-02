<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResellerSettingsRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email',
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $digits = preg_replace('/\D/', '', $value);
                    if (strlen($digits) < 10 || strlen($digits) > 15) {
                        $fail('The phone number must contain 10 to 15 digits.');
                    }
                },
            ],
            'street_address' => 'required|string|min:5|max:255',
            'city' => 'required|string|min:2|max:100',
            'state' => 'required|string|min:2|max:100',
            'postal_code' => 'required|string|min:3|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Phone number is required.',
            'street_address.required' => 'Street address is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State/Province is required.',
            'postal_code.required' => 'ZIP/Postal code is required.',
        ];
    }
}
