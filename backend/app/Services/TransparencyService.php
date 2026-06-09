<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\CampaignExpense;
use Illuminate\Support\Facades\Cache;

class TransparencyService
{
    /**
     * Obtiene el listado general de campañas activas para el módulo de transparencia
     */
    public function getActiveCampaigns()
    {
        return Cache::remember('transparency_campaigns', 1800, function () {
            return Campaign::where('status', 'active')
                ->orWhere('status', 'completed')
                ->select(['id', 'slug', 'name', 'status', 'monetary_goal', 'image_path'])
                ->get()
                ->map(function ($campaign) {
                    $campaign->total_recaudado = Donation::where('campaign_id', $campaign->id)
                        ->where('status', 'succeeded')
                        ->sum('amount');
                    $campaign->progress_percentage = $campaign->monetary_goal > 0 
                        ? min(100, round(($campaign->total_recaudado / $campaign->monetary_goal) * 100, 2))
                        : 0;
                    return $campaign;
                });
        });
    }

    /**
     * Obtiene los detalles de trazabilidad de una campaña específica
     */
    public function getCampaignDetails($slug)
    {
        return Cache::remember("transparency_campaign_{$slug}", 1800, function () use ($slug) {
            $campaign = Campaign::where('slug', $slug)
                ->with(['expenses' => function ($query) {
                    $query->orderBy('date', 'desc')->orderBy('id', 'desc');
                }])
                ->firstOrFail();

            $total_recaudado = Donation::where('campaign_id', $campaign->id)
                ->where('status', 'succeeded')
                ->sum('amount');

            $total_ejecutado = $campaign->expenses->sum('amount');

            $saldo_disponible = $total_recaudado - $total_ejecutado;

            // Anonymized recent donations
            $recent_donations = Donation::where('campaign_id', $campaign->id)
                ->where('status', 'succeeded')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($donation) {
                    return [
                        'amount' => $donation->amount,
                        'date' => $donation->created_at->format('Y-m-d'),
                        'donor_name' => $donation->is_anonymous ? 'Anónimo' : ($donation->donor->first_name ?? 'Anónimo'),
                    ];
                });

            return [
                'metadata' => [
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'monetary_goal' => $campaign->monetary_goal,
                    'report_pdf_url' => $campaign->report_pdf_path ? asset('storage/' . $campaign->report_pdf_path) : null,
                ],
                'cifras' => [
                    'total_recaudado' => round($total_recaudado, 2),
                    'total_ejecutado' => round($total_ejecutado, 2),
                    'saldo_disponible' => round($saldo_disponible, 2),
                ],
                'trazabilidad' => $campaign->expenses->map(function ($expense) {
                    return [
                        'date' => $expense->date,
                        'description' => $expense->description,
                        'amount' => $expense->amount,
                    ];
                }),
                'donaciones_recientes' => $recent_donations
            ];
        });
    }
}
