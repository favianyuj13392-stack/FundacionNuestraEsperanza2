<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CampaignExpense extends Model
{
    protected $fillable = ['campaign_id', 'amount', 'date', 'description'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    protected static function booted()
    {
        static::saved(function ($expense) {
            Cache::forget('transparency_campaigns');
            if ($expense->campaign) {
                Cache::forget('transparency_campaign_' . $expense->campaign->slug);
            }
        });

        static::deleted(function ($expense) {
            Cache::forget('transparency_campaigns');
            if ($expense->campaign) {
                Cache::forget('transparency_campaign_' . $expense->campaign->slug);
            }
        });
    }
}
