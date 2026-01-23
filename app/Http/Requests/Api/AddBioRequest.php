<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddBioRequest extends FormRequest
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
            'name' => 'required|string|min:4',
            'dob' => 'required|date',
            'dod' => 'nullable|date',
            'profile_picture' => 'required|image|mimes:png,jpg',
            'cover_picture' => 'required|image|mimes:png,jpg',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'spotify' => 'nullable|url',
            'youtube' => 'nullable|url',
            'bio' => 'required|string',
            'longitude' => 'required|string',
            'latitude' => 'required|string',
            'link_id' => 'required|exists:links,id'
        ];
    }
}
