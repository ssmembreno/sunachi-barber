<?php

namespace App\Http\Controllers\Barbers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Barbers\BarberRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Barber;

class BarberController extends Controller
{
    public function index()
    {

        $barbers = Barber::all();

        return Inertia::render('Barbers/Index', [
            'barbers' => $barbers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Barbers/Create');
    }


    public function store(BarberRequest $barber)
    {
        $barber = Barber::create($barber->validated());

        return redirect()->route('barbers.index');
    }

    public function edit(Barber $barber)
    {
        return Inertia::render('Barbers/Edit', [
            'barber' => $barber,
        ]);
    }

    public function update(BarberRequest $request, Barber $barber)
    {
        $barber->update($request->validated());

        return redirect()->route('barbers.index');
    }
}
