<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrDataRequest extends FormRequest
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
            'qr_price' => 'required|numeric|min:1',
            'min_quantity' => 'required|numeric|min:1',
            'max_quantity' => 'required|numeric|gte:min_quantity',
        ];
    }
}
