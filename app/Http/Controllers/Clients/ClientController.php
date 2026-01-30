<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Http\Requests\Client\ClientRequest;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(ClientRequest $request)
    {

        Client::create($request->validated());

    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());
    }

    public function destroy(Client $client)
    {
        $client->delete();
    }

    public function trash(Request $request)
    {
        $clients = Client::onlyTrashed()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%"))
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/RestoreClients', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function restore(Client $client)
    {
        $client->restore();
        return back();
    }

    public function forceDelete(Client $client)
    {
        $client->forceDelete();
        return back();
    }

}
