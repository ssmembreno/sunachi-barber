<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Services;
use Illuminate\Validation\ValidationException;

class CalendarClientController extends Controller
{
    public function index()
    {
        $services = Services::all(); // Optionally filter active
        $barbers = \App\Models\User::whereIn('role', ['admin', 'barber'])->get();

        $lastReservations = [];
        $user = Auth::user();

        if ($user) {
            $lastReservations = Appointment::with(['service', 'barber', 'client'])
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                    
                    if ($user->client_id) {
                        $query->orWhere('client_id', $user->client_id);
                    }
                    
                })
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();
        }

        return Inertia::render('Client/Calendar/CalendarClient', [
            'services' => $services,
            'barbers' => $barbers,
            'lastReservations' => $lastReservations,
        ]);
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'barber_id' => ['nullable', 'integer'],
        ]);

        $q = Appointment::query()
            ->with(['service', 'client']) // Added client for email check
            ->where('start_at', '>=', $request->start)
            ->where('start_at', '<', $request->end)
            ->whereNotIn('status', ['cancelled']);

        if ($request->filled('barber_id')) {
            $q->where('barber_id', $request->barber_id);
        }

        $user = Auth::user();
        $isAdmin = $user && in_array($user->role, ['admin', 'barber']);

        $appointments = $q->get();
        
        $events = $appointments->map(function ($a) use ($user, $isAdmin) {
            $isMine = false;

            if ($user && !$isAdmin) {
                $checkId = $a->user_id && (int)$a->user_id === (int)$user->id;
                $checkClient = $user->client_id && (int)$a->client_id === (int)$user->client_id;
                $checkEmail = $a->client && $user->email && strtolower($a->client->email) === strtolower($user->email);
                
                $isMine = $checkId || $checkClient || $checkEmail;
            }

            $title = 'Ocupado';
            $serviceName = $a->service->name ?? 'Servicio';

            if ($isMine || $isAdmin) {
                 if ($isAdmin) {
                     $clientName = $a->client ? $a->client->name : 'Cliente';
                     $title = "$clientName - $serviceName";
                 } else {
                     $title = "Tu Cita: $serviceName";
                 }
            }

            // Colors
            $bgColor = $isAdmin ? null : ($isMine ? 'rgb(245 153 11)' : '#27272a');

            // Props
            $extendedProps = [
                'status' => $a->status,
                'is_mine' => $isMine,
                'barber_id' => $a->barber_id,
            ];

            // Add private details only if allowed
            if ($isMine || $isAdmin) {
                $extendedProps['service_name'] = $serviceName;
                $extendedProps['price'] = $a->service->price ?? null;
                $extendedProps['notes'] = $a->client_notes;
                
                // Add client info for admin or self? mainly for admin
                if ($isAdmin && $a->client) {
                     $extendedProps['client_name'] = $a->client->name;
                     $extendedProps['client_phone'] = $a->client->phone;
                }
            }

            return [
                'id' => (string) $a->id,
                'title' => $title,
                'start' => $a->start_at?->toISOString(),
                'end' => $a->end_at?->toISOString(),
                'display' => 'auto',
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'extendedProps' => $extendedProps,
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
{
    $user = Auth::user();

    $rules = [
        'service_id' => ['required', 'integer', 'exists:services,id'],
        'barber_id'  => ['required', 'integer', 'exists:users,id'],
        'start_at'   => ['required', 'date'],
        'end_at'     => ['required', 'date', 'after:start_at'],
        'client_notes' => ['nullable', 'string', 'max:1000'],
    ];

    // Si NO está logeado, pedimos nombre y teléfono
    if (!$user) {
        $rules['client_name']  = ['required', 'string', 'max:255'];
        $rules['client_phone'] = ['required', 'string', 'max:20'];
    }

    $data = $request->validate($rules);

    $start = Carbon::parse($data['start_at']);
    $end   = Carbon::parse($data['end_at']);

    // 1) Resolver CLIENT (logeado → por client_id, guest → por phone)
    if ($user) {
        if (!$user->client_id) {
            throw ValidationException::withMessages([
                'client' => 'Tu cuenta no tiene cliente asociado. Contacta con soporte.',
            ]);
        }
        $client = Client::findOrFail($user->client_id);
    } else {
        $client = Client::firstOrCreate(
            ['phone' => $data['client_phone']],
            ['name' => $data['client_name']]
        );
    }

    // 2) Bloquear solapes
    $hasOverlap = Appointment::query()
        ->where('barber_id', $data['barber_id'])
        ->whereNotIn('status', ['cancelled'])
        ->where(function ($q) use ($start, $end) {
            $q->where('start_at', '<', $end)
              ->where('end_at', '>', $start);
        })
        ->exists();

    if ($hasOverlap) {
        return response()->json(['message' => 'Ese horario ya está ocupado.'], 422);
    }

    // 3) Crear cita
    $appointment = Appointment::create([
        'client_id' => $client->id,
        'barber_id' => $data['barber_id'],
        'service_id' => $data['service_id'],
        'start_at' => $start,
        'end_at' => $end,
        'status' => 'pending',
        'source' => $user ? 'client_auth' : 'client_guest',
        'client_notes' => $data['client_notes'] ?? null,

        'user_id' => $user?->id,
        'created_by' => $user?->id,
    ]);

    return response()->json([
        'message' => 'Reserva creada.',
        'appointment' => $appointment->id,
    ], 201);
}
}
