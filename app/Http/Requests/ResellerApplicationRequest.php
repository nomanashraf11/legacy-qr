<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResellerApplicationRequest extends FormRequest
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
            'business_name' => 'required|string|min:2|max:255',
            'business_category' => 'required|string|min:2|max:255',
            'years_in_business' => 'nullable|integer|min:0|max:100',
            'street_address' => 'required|string|min:5|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'business_phone' => 'nullable|string|max:30',
            'website' => 'nullable|string|max:255',
            'full_name' => 'required|string|min:2|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'estimated_monthly_volume' => 'nullable|string|max:100',
            'hear_about_us' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'street_address.required' => 'Street address is required.',
            'street_address.min' => 'Street address must be at least 5 characters.',
        ];
    }
}
