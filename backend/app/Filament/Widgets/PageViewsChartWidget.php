<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class PageViewsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Comparativa de Vistas Diarias';
    protected static ?int $sort = 2;

    protected function getFilters(): ?array
    {
        $filters = [];
        // Generar los últimos 12 meses como opciones
        for ($i = 0; $i <= 11; $i++) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $filters[$key] = ucfirst($date->translatedFormat('F Y'));
        }
        return $filters;
    }

    protected function getData(): array
    {
        // Mes seleccionado por defecto (el actual)
        $filter = $this->filter ?? Carbon::now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $filter);
        
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        // Limitar el fin del gráfico hasta hoy si el mes es el actual
        if ($end->isFuture()) {
            $end = Carbon::today();
        }

        // Obtener vistas agrupadas por día
        $visits = PageVisit::selectRaw('DATE(visited_on) as date, COUNT(*) as count')
            ->whereBetween('visited_on', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        $data = [];
        $labels = [];

        // Llenar los datos para cada día del mes
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $dateString = $day->format('Y-m-d');
            $labels[] = $day->format('d');
            $data[] = $visits->get($dateString, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Vistas',
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
