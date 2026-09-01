<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\CampaignStatsService;
use Illuminate\Http\Request;

class PublicCampaignController extends Controller
{
    protected $statsService;

    public function __construct(CampaignStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Devuelve las campañas activas con su progreso calculado (Cache-aside).
     */
    public function index()
    {
        $campaigns = Campaign::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($campaign) {
                $stats = $this->statsService->getCampaignProgress($campaign->id);
                
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'slug' => $campaign->slug,
                    'description' => $campaign->description,
                    'image' => $campaign->image_path ? (str_starts_with($campaign->image_path, 'http') ? $campaign->image_path : url('storage/' . $campaign->image_path)) : null,
                    'goal' => $stats['goal'],
                    'raised' => $stats['raised'],
                    'percentage' => $stats['percentage'],
                    'start_date' => $campaign->start_date ? $campaign->start_date->format('Y-m-d') : null,
                    'end_date' => $campaign->end_date ? $campaign->end_date->format('Y-m-d') : null,
                    'allowed_frequencies' => $campaign->allowed_frequencies,
                    'allowed_payment_methods' => $campaign->allowed_payment_methods,
                ];
            });

        return response()->json($campaigns);
    }
}
