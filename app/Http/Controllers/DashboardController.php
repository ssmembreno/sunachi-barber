<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Appointment;
use App\Models\AppointmentItem;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tz = 'America/Tegucigalpa';
        
        // Fecha seleccionada o el día de hoy
        $selectedDate = $request->query('date') ? \Carbon\Carbon::parse($request->query('date'), $tz) : \Carbon\Carbon::now($tz);
        
        $todayStart = $selectedDate->copy()->startOfDay()->utc();
        $todayEnd = $selectedDate->copy()->endOfDay()->utc();

        // Capital Obtenido = Ventas directas del día + Citas completadas del día
        $salesCapital = Sale::whereBetween('sold_at', [$todayStart, $todayEnd])->sum('amount');
        
        $appointmentsCapital = AppointmentItem::whereHas('appointment', function($q) use ($todayStart, $todayEnd) {
            $q->where('status', 'completed')
              ->whereBetween('start_at', [$todayStart, $todayEnd]);
        })->sum('price');
        
        $totalCapital = $salesCapital + $appointmentsCapital;

        $appointmentsPercentage = $totalCapital > 0 ? round(($appointmentsCapital / $totalCapital) * 100, 1) : 0;
        $salesPercentage = $totalCapital > 0 ? round(($salesCapital / $totalCapital) * 100, 1) : 0;

        $completedAppointments = Appointment::whereBetween('start_at', [$todayStart, $todayEnd])->where('status', 'completed')->count();
        $pendingAppointments = Appointment::whereBetween('start_at', [$todayStart, $todayEnd])->whereIn('status', ['pending', 'confirmed'])->count();
        $cancelledAppointments = Appointment::whereBetween('start_at', [$todayStart, $todayEnd])->whereIn('status', ['cancelled', 'noshow'])->count();

        // String for display and selected value
        $today = $selectedDate->isToday() ? 'Hoy' : $selectedDate->format('d/m/Y');
        $dateValue = $selectedDate->format('Y-m-d');

        // ==== Ingresos Mensuales del Año (Chart) ====
        $month = $selectedDate->month;
        $year = $selectedDate->year;
        
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, $tz);
        $daysInMonth = $startOfMonth->daysInMonth;
        
        $startOfMonthUtc = $startOfMonth->copy()->startOfDay()->utc();
        $endOfMonthUtc = $startOfMonth->copy()->endOfMonth()->endOfDay()->utc();

        // Inicializar array de totales diarios a 0
        $dailyTotals = array_fill(1, $daysInMonth, 0);

        // Ventas
        $sales = Sale::whereBetween('sold_at', [$startOfMonthUtc, $endOfMonthUtc])->get(['amount', 'sold_at']);
        foreach ($sales as $sale) {
            $day = \Carbon\Carbon::parse($sale->sold_at)->setTimezone($tz)->day;
            $dailyTotals[$day] += $sale->amount;
        }

        // Citas completadas
        $apps = Appointment::where('status', 'completed')
            ->whereBetween('start_at', [$startOfMonthUtc, $endOfMonthUtc])
            ->with('items:id,appointment_id,price')
            ->get(['id', 'start_at']);
            
        foreach ($apps as $app) {
            $day = \Carbon\Carbon::parse($app->start_at)->setTimezone($tz)->day;
            $dailyTotals[$day] += $app->items->sum('price');
        }

        // Formatear etiquetas y mes
        $monthName = ucfirst($selectedDate->locale('es')->monthName);
        $labels = range(1, $daysInMonth);
        
        $chartData = [
            'labels' => $labels,
            'data' => array_values($dailyTotals),
            'monthName' => $monthName,
            'year' => $year,
            'total_month' => array_sum($dailyTotals),
        ];

        return Inertia::render('Dashboard', [
            'totalCapital' => $totalCapital,
            'appointmentsPercentage' => $appointmentsPercentage,
            'salesPercentage' => $salesPercentage,
            'completedAppointments' => $completedAppointments,
            'pendingAppointments' => $pendingAppointments,
            'cancelledAppointments' => $cancelledAppointments,
            'today' => $today,
            'dateValue' => $dateValue,
            'chartData' => $chartData,
        ]);
    }

    public function export(Request $request)
    {
        $tz = 'America/Tegucigalpa';
        $selectedDate = $request->query('date') ? \Carbon\Carbon::parse($request->query('date'), $tz) : \Carbon\Carbon::now($tz);
        
        $todayStart = $selectedDate->copy()->startOfDay()->utc();
        $todayEnd = $selectedDate->copy()->endOfDay()->utc();

        $fileName = 'Reporte_Ingresos_' . $selectedDate->format('d-m-Y') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function() use ($todayStart, $todayEnd, $tz) {
            $file = fopen('php://output', 'w');
            
            // utf-8 BOM para soporte correcto de caracteres especiales en Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // Encabezados
            fputcsv($file, ['Tipo', 'Hora', 'Cliente', 'Profesional (Barbero)', 'Estado / Metodo', 'Ingreso Obtenido (HNL)']);

            // Citas
            $apps = Appointment::where('status', 'completed')
                ->whereBetween('start_at', [$todayStart, $todayEnd])
                ->with(['client', 'barber', 'items'])
                ->orderBy('start_at', 'asc')
                ->get();
                
            $totalApps = 0;
            foreach ($apps as $app) {
                $price = $app->items->sum('price');
                $totalApps += $price;
                
                fputcsv($file, [
                    'Cita Finalizada',
                    \Carbon\Carbon::parse($app->start_at)->setTimezone($tz)->format('h:i A'),
                    $app->client ? $app->client->name : 'General',
                    $app->barber ? $app->barber->name : 'N/A',
                    ucfirst($app->status),
                    $price
                ]);
            }

            // Ventas
            $sales = Sale::whereBetween('sold_at', [$todayStart, $todayEnd])
                ->with(['client', 'barber'])
                ->orderBy('sold_at', 'asc')
                ->get();
                
            $totalSales = 0;
            foreach ($sales as $sale) {
                $totalSales += $sale->amount;
                
                fputcsv($file, [
                    'Venta Directa',
                    \Carbon\Carbon::parse($sale->sold_at)->setTimezone($tz)->format('h:i A'),
                    $sale->client ? $sale->client->name : 'General',
                    $sale->barber ? $sale->barber->name : 'N/A',
                    ucfirst($sale->payment_method),
                    $sale->amount
                ]);
            }
            
            fputcsv($file, ['', '', '', '', '', '']); // Fila separadora
            fputcsv($file, ['RESUMEN DEL DIA', '', '', '', 'Total Citas:', $totalApps]);
            fputcsv($file, ['', '', '', '', 'Total Ventas:', $totalSales]);
            fputcsv($file, ['', '', '', '', 'CAPITAL GLOBAL:', $totalApps + $totalSales]);
            
            fclose($file);
        }, 200, $headers);
    }
}
 