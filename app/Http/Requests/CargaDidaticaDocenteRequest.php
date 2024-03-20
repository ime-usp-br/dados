<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaDidaticaDocenteRequest extends FormRequest
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
            'departamento' => 'nullable|in:MAT,MAC,MAP,MAE',
            'ano' => 'nullable|numeric|max:'.date("Y").'|min:2000',
            'semestre' => 'nullable|in:1,2',
        ];

        return $rules;
    }
}
