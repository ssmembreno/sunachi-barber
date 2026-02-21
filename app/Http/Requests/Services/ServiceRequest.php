<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'description.required' => 'La descripción es requerida',
            'description.max' => 'La descripción debe tener menos de 255 caracteres',
            'price.required' => 'El precio es requerido',
            'price.numeric' => 'El precio debe ser un numero',
            'duration_minutes.required' => 'La duración es requerida',
            'duration_minutes.numeric' => 'La duración debe ser un numero',

        ];
    }
}
