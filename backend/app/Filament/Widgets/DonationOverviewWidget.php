<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DonationOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Recaudación Total BOB (currency_id = 1)
        $totalBob = Donation::where('currency_id', 1)
            ->where('status', 'succeeded')
            ->sum('amount');

        // 2. Recaudación Total USD (currency_id = 2)
        $totalUsd = Donation::where('currency_id', 2)
            ->where('status', 'succeeded')
            ->sum('amount');

        // 3. Recaudación Este Mes
        $thisMonth = Carbon::now()->startOfMonth();
        $bobThisMonth = Donation::where('currency_id', 1)
            ->where('status', 'succeeded')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');
        $usdThisMonth = Donation::where('currency_id', 2)
            ->where('status', 'succeeded')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        // 4. Conteo de Donaciones Exitosas y Recurrentes
        $countTotal = Donation::where('status', 'succeeded')->count();
        $countRecurring = Donation::where('status', 'succeeded')->where('is_recurring', true)->count();

        return [
            Stat::make('Total Recaudado (BOB)', 'Bs. ' . number_format($totalBob, 2))
                ->description('Bs. ' . number_format($bobThisMonth, 2) . ' ingresados este mes')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Total Recaudado (USD)', '$ ' . number_format($totalUsd, 2))
                ->description('$ ' . number_format($usdThisMonth, 2) . ' ingresados este mes')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Donaciones', number_format($countTotal))
                ->description($countRecurring . ' recurrentes (' . ($countTotal > 0 ? round(($countRecurring / $countTotal) * 100) : 0) . '%)')
                ->descriptionIcon('heroicon-m-heart')
                ->color('primary'),
        ];
    }
}
