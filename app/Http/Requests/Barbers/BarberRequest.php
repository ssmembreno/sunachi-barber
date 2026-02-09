<?php

namespace App\Http\Requests\Barbers;

use Illuminate\Foundation\Http\FormRequest;

class BarberRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'notes' => 'nullable',
            'is_active' => 'required',
        ];

    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser un email valido',
            'phone.required' => 'El telefono es obligatorio',
            'is_active.required' => 'El estado es obligatorio',
        ];
    }
}
