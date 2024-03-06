<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscentesIngressantesRequest extends FormRequest
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
        $rules = [
            'codcurhab' => 'nullable',
            'ano' => 'nullable|numeric|max:'.date("Y").'|min:2000',
        ];

        return $rules;
    }
}
