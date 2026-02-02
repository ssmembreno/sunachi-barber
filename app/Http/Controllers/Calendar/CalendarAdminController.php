<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Services;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AdminCalendar\CalendarAdminRequest;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarAdminController extends Controller
{
    public function events(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'barber_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
        ]);

        $q = Appointment::query()
            ->with(['client:id,name', 'service:id,name'])
            ->where('start_at', '>=', $request->start)
            ->where('start_at', '<', $request->end);

        // // filtrar por barbero
        // if ($request->filled('barber_id')) {
        //     $q->where('barber_id', $request->barber_id);
        // }

        // // filtrar por estado
        // if ($request->filled('status')) {
        //     $q->where('status', $request->status);
        // }

        $appointments = $q->get();

        // Formato FullCalendar
        $events = $appointments->map(function ($a) {
            $clientName = $a->client->name ?? 'Cliente';
            $serviceName = $a->service->name ?? 'Servicio';

            return [
                'id' => (string) $a->id,
                'title' => $serviceName . ' - ' . $clientName,
                'start' => $a->start_at?->toISOString(),
                'end' => $a->end_at?->toISOString(),
                'extendedProps' => [
                    'status' => $a->status,
                    'source' => $a->source,
                    'client_id' => $a->client_id,
                    'barber_id' => $a->barber_id,
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(CalendarAdminRequest $request){
        $validated = $request->validated();

        $start = Carbon::parse($validated['start_at']);
        $end   = Carbon::parse($validated['end_at']);

    // Bloquear solapes para el mismo barbero (excepto canceladas)
    $hasOverlap = Appointment::query()
        ->where('barber_id', $validated['barber_id'])
        ->whereNotIn('status', ['cancelled'])
        ->where(function ($q) use ($start, $end) {
            $q->where('start_at', '<', $end)
              ->where('end_at', '>', $start);
        })
        ->exists();

    if ($hasOverlap) {
        return response()->json([
            'message' => 'Ese horario ya está ocupado.',
        ], 422);
    }
        $appointment = Appointment::create([
            ...$validated,
            'start_at' => $start,
            'end_at' => $end,
            'status' => 'confirmed',
        ]);
        return response()->json([
            'message' => 'Cita creada correctamente.',
            'appointment' => $appointment->id,
        ], 201);
    }

    public function formData()
    {
        return response()->json([
            'clients' => Client::orderBy('name')->get(['id', 'name', 'phone']),
            'services' => Services::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'barbers' => User::all(['id', 'name']),
            'current_user_id' => Auth::id(),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|string|in:confirmed,pending,cancelled,completed,noshow'
        ]);

        $appointment->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'status' => $appointment->status
        ]);
    }
}
