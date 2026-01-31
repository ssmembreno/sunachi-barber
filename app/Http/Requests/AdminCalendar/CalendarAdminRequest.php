<?php

namespace App\Http\Requests\AdminCalendar;

use Illuminate\Foundation\Http\FormRequest;

class CalendarAdminRequest extends FormRequest
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
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'status' => 'required|string',
            'source' => 'required|string',
            'notes' => 'nullable|string',
            'client_notes' => 'nullable|string',
            'price' => 'nullable|numeric',
            'cancelled_at' => 'nullable|date',
            'cancel_reason' => 'nullable|string',
            'client_id' => 'required|integer',
            'client_user_id' => 'nullable|integer',
            'service_id' => 'required|integer',
            'created_by' => 'required|integer',
            'barber_id' => 'required|integer',
            'meta' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'start_at.required' => 'La fecha de inicio es obligatoria.',
            'start_at.date' => 'La fecha de inicio debe ser una fecha válida.',
            'end_at.required' => 'La fecha de fin es obligatoria.',
            'end_at.date' => 'La fecha de fin debe ser una fecha válida.',
            'status.required' => 'El estado es obligatorio.',
            'status.string' => 'El estado debe ser una cadena de texto.',
            'source.required' => 'La fuente es obligatoria.',
            'source.string' => 'La fuente debe ser una cadena de texto.',
            'notes.string' => 'Las notas deben ser una cadena de texto.',
            'client_notes.string' => 'Las notas del cliente deben ser una cadena de texto.',
            'price.numeric' => 'El precio debe ser un número.',
            'cancelled_at.date' => 'La fecha de cancelación debe ser una fecha válida.',
            'cancel_reason.string' => 'La razón de cancelación debe ser una cadena de texto.',
            'client_id.required' => 'El cliente es obligatorio.',
            'client_id.integer' => 'El cliente debe ser un número entero.',
            'client_user_id.integer' => 'El cliente debe ser un número entero.',
            'service_id.required' => 'El servicio es obligatorio.',
            'service_id.integer' => 'El servicio debe ser un número entero.',
            'created_by.required' => 'El creador es obligatorio.',
            'created_by.integer' => 'El creador debe ser un número entero.',
            'barber_id.required' => 'El barbero es obligatorio.',
            'barber_id.integer' => 'El barbero debe ser un número entero.',
            'meta.array' => 'La meta debe ser un array.',
        ];
    }
}
