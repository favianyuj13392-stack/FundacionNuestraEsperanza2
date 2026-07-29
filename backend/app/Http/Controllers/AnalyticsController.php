<?php

namespace App\Http\Controllers;

use App\Models\PageVisit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function trackVisit(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|string|max:255',
        ]);

        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $today = Carbon::today();

        // Evitar contar la misma página varias veces para la misma IP en el mismo día
        // Si quieres contar cada recarga, quita este check. Pero para "Visitantes Únicos por página por día":
        $exists = PageVisit::where('url_visited', $validated['url'])
            ->where('ip_address', $ip)
            ->where('visited_on', $today)
            ->exists();

        if (!$exists) {
            PageVisit::create([
                'url_visited' => $validated['url'],
                'ip_address'  => $ip,
                'user_agent'  => $userAgent,
                'visited_on'  => $today,
            ]);
        }

        return response()->json(['status' => 'tracked']);
    }
}
