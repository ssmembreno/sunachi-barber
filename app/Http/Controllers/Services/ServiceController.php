<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Services;
use App\Http\Requests\Services\ServiceRequest;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Services::all();

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        return Inertia::render('Services/Create');
    }

    public function store(ServiceRequest $request)
    {
        Services::create($request->validated());

        return redirect()->route('services.index');
    }

    public function edit(Services $service)
    {
        return Inertia::render('Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(ServiceRequest $request, Services $service)
    {
        $service->update($request->validated());

        return redirect()->route('services.index');
    }

    public function destroy(Services $service)
    {
        $service->delete();

        return redirect()->route('services.index');
    }
}
