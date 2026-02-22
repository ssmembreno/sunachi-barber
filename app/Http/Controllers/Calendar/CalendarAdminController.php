<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Services;
use App\Models\Barber;
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
            ->with(['client:id,name', 'items.service:id,name', 'barber:id,name'])
            ->where('start_at', '>=', $request->start)
            ->where('start_at', '<', $request->end);

        $appointments = $q->get();

        // Formato FullCalendar
        $events = $appointments->map(function ($a) {
            $clientName = $a->client->name ?? 'Cliente';
            $serviceNames = $a->items->map(fn($item) => $item->service->name ?? 'Servicio')->implode(' + ');
            if (empty($serviceNames)) $serviceNames = 'Servicio';
            $barberName = $a->barber->name ?? 'Barbero';

            return [
                'id' => (string) $a->id,
                'title' => $serviceNames . ' - ' . $clientName . ' - ' . $barberName,
                'start' => $a->start_at?->toISOString(),
                'end' => $a->end_at?->toISOString(),
                'extendedProps' => [
                    'status' => $a->status,
                    'source' => $a->source,
                    'client_id' => $a->client_id,
                    'barber_id' => $a->barber_id,
                    'price' => $a->items->sum('price'),
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
            'start_at' => $start,
            'end_at' => $end,
            'status' => 'confirmed',
            'source' => $validated['source'],
            'notes' => $validated['notes'] ?? null,
            'client_notes' => $validated['client_notes'] ?? null,
            'client_id' => $validated['client_id'],
            'client_user_id' => $validated['client_user_id'] ?? null,
            'created_by' => $validated['created_by'],
            'barber_id' => $validated['barber_id'],
            'meta' => $validated['meta'] ?? null,
        ]);
        
        foreach ($validated['services'] as $item) {
            $appointment->items()->create([
                'service_id' => $item['service_id'],
                'price' => $item['price'],
            ]);
        }

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
            'barbers' => Barber::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'current_user_id' => Auth::id(),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|string|in:confirmed,pending,cancelled,completed,noshow'
        ]);

        $updateData = ['status' => $request->status];
        if ($request->status === 'cancelled') {
            $updateData['cancelled_at'] = now();
            $updateData['cancel_reason'] = 'Cancelado por administrador';
        }

        $appointment->update($updateData);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'status' => $appointment->status
        ]);
    }
}
