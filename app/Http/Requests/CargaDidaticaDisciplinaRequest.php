<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaDidaticaDisciplinaRequest extends FormRequest
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
        $periodo_max = date("Y") . ((date("m") < 7) ? "1" : "2");
        $rules = [
            'departamento' => 'nullable|in:MAT,MAC,MAP,MAE',
            'periodo_inicial' => 'nullable|numeric|max:'.$periodo_max.'|min:20001',
            'periodo_final' => 'nullable|numeric|max:'.$periodo_max.'|gt:periodo_inicial',
        ];

        return $rules;
    }
}
