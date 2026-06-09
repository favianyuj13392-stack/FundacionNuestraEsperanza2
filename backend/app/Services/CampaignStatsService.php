<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Support\Facades\Cache;

class CampaignStatsService
{
    /**
     * Obtiene el progreso de una campaña utilizando Cache-aside.
     * Retorna el monto recaudado, la meta y el porcentaje de avance.
     *
     * @param int $campaignId
     * @return array
     */
    public function getCampaignProgress(int $campaignId): array
    {
        $cacheKey = "campaign_stats_{$campaignId}";
        $ttl = now()->addMinutes(30);

        return Cache::remember($cacheKey, $ttl, function () use ($campaignId) {
            return $this->calculateProgressFromDb($campaignId);
        });
    }

    /**
     * Calcula estrictamente en modo de solo lectura (Read-only) el progreso de la campaña.
     *
     * @param int $campaignId
     * @return array
     */
    private function calculateProgressFromDb(int $campaignId): array
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return [
                'raised' => 0,
                'goal' => 0,
                'percentage' => 0,
            ];
        }

        // Sumarizar donaciones completadas (estado: succeeded) de esta campaña
        $raised = Donation::where('campaign_id', $campaignId)
            ->where('status', 'succeeded')
            ->sum('amount');

        $goal = $campaign->monetary_goal ?: 0;
        
        $percentage = $goal > 0 ? min(100, round(($raised / $goal) * 100, 2)) : 0;

        return [
            'raised' => (float) $raised,
            'goal' => (float) $goal,
            'percentage' => (float) $percentage,
        ];
    }
    
    /**
     * Invalida la caché de una campaña.
     * Esto puede usarse si se requiere forzar actualización manual desde el panel admin.
     *
     * @param int $campaignId
     * @return void
     */
    public function clearCampaignCache(int $campaignId): void
    {
        Cache::forget("campaign_stats_{$campaignId}");
    }
}
