<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Calendar\CalendarAdminController;
use App\Http\Controllers\Calendar\CalendarClientController;
use App\Http\Controllers\Barbers\BarberController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\Sales\SalesController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');


Route::middleware(['auth', 'verified', 'role:admin,barber'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // Calendar
    Route::get('/calendar', function() {return Inertia::render('Admin/Calendar/CalendarAdmin');})->name('calendar.admin');
    Route::get('/calendar/events', [CalendarAdminController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/formData', [CalendarAdminController::class, 'formData'])->name('calendar.formData');
    Route::post('/calendar/appointmentsCreate', [CalendarAdminController::class, 'store'])->name('calendar.store');
    Route::patch('/calendar/appointments/{appointment}/status', [CalendarAdminController::class, 'updateStatus'])->name('calendar.updateStatus');


    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('/clients/trash', [ClientController::class, 'trash'])->name('clients.trash');
    Route::put('/clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore')->withTrashed();
    Route::delete('/clients/{client}/forceDelete', [ClientController::class, 'forceDelete'])->name('clients.forceDelete')->withTrashed();

    // Barbers
    Route::get('/barbers', [BarberController::class, 'index'])->name('barbers.index');
    Route::get('/barbers/create', [BarberController::class, 'create'])->name('barbers.create');
    Route::post('/barbers', [BarberController::class, 'store'])->name('barbers.store');
    Route::get('/barbers/{barber}/edit', [BarberController::class, 'edit'])->name('barbers.edit');
    Route::put('/barbers/{barber}', [BarberController::class, 'update'])->name('barbers.update');
    Route::delete('/barbers/{barber}', [BarberController::class, 'destroy'])->name('barbers.destroy');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');


    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}/edit', [SalesController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{sale}', [SalesController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
});

    // calendar clients
    Route::get('/booking', [CalendarClientController::class, 'index'])->name('booking.index');
    Route::get('/booking/events', [CalendarClientController::class, 'events'])->name('booking.events');
    Route::post('/booking/appointments', [CalendarClientController::class, 'store'])->name('booking.appointments.store');
    Route::post('/booking/appointments/{appointment}/cancel', [CalendarClientController::class, 'cancel'])->name('booking.appointments.cancel');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
