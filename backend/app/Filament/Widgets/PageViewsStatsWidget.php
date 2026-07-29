<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PageViewsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $viewsToday = PageVisit::where('visited_on', $today)->count();
        $viewsThisMonth = PageVisit::where('visited_on', '>=', $thisMonth)->count();
        $viewsTotal = PageVisit::count();

        return [
            Stat::make('Vistas de Hoy', number_format($viewsToday))
                ->description('Páginas cargadas hoy')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            
            Stat::make('Vistas este Mes', number_format($viewsThisMonth))
                ->description('En lo que va de ' . strtolower(Carbon::now()->translatedFormat('F')))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            
            Stat::make('Vistas Totales', number_format($viewsTotal))
                ->description('Histórico completo')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),
        ];
    }
}
