<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLinkRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'number' => 'required|integer|min:1',
            'version_type' => 'required|in:full,christmas',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Batch name is required.',
            'number.required' => 'Number of QR codes is required.',
            'number.integer' => 'Number must be an integer.',
            'number.min' => 'Number must be at least 1.',
            'version_type.required' => 'Version type is required.',
            'version_type.in' => 'Version type must be either full or christmas.',
        ];
    }
}
