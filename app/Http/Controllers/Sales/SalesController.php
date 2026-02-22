<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sale;
use App\Models\Barber;
use App\Models\Services;
use App\Models\Client;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index(Request $request) 
    {
        $query = Sale::with(['barber', 'items.service', 'client']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('barber', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('items.service', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('client_name', 'like', "%{$search}%");
        }

        $sales = $query->orderBy('sold_at', 'desc')->paginate(10);

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['search']),
             // Data for modals
            'barbers' => Barber::where('is_active', true)->get(),
            'services' => Services::where('is_active', true)->get(),
            'clients' => Client::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barber_id' => 'required|exists:barber,id',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.price' => 'required|numeric|min:0',
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,transfer,other',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::create([
            'barber_id' => $validated['barber_id'],
            'client_id' => $validated['client_id'],
            'client_name' => $validated['client_name'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
            'sold_at' => now(),
        ]);

        foreach ($validated['services'] as $item) {
            $sale->items()->create([
                'service_id' => $item['service_id'],
                'price' => $item['price']
            ]);
        }

        return redirect()->back()->with('success', 'Venta registrada exitosamente.');
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'barber_id' => 'required|exists:barber,id',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.price' => 'required|numeric|min:0',
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,transfer,other',
            'notes' => 'nullable|string',
        ]);

        $sale->update([
            'barber_id' => $validated['barber_id'],
            'client_id' => $validated['client_id'],
            'client_name' => $validated['client_name'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
        ]);

        // Recreate items
        $sale->items()->delete();
        foreach ($validated['services'] as $item) {
            $sale->items()->create([
                'service_id' => $item['service_id'],
                'price' => $item['price']
            ]);
        }

        return redirect()->back()->with('success', 'Venta actualizada exitosamente.');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->back()->with('success', 'Venta eliminada exitosamente.');
    }
}
