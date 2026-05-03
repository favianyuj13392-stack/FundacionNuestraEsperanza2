<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    /**
     * Get the donation history of the authenticated user.
     * This endpoint requires authentication (e.g., Sanctum).
     */
    public function myDonations(Request $request)
    {
        // 1. Get the authenticated User.
        $user = Auth::user();
        \Illuminate\Support\Facades\Log::info('myDonations hit', ['user_id' => $user ? $user->id : 'null']);

        // Check if the user has a Donor profile
        if (!$user || !$user->donor) {
            return response()->json(['message' => 'No donor profile found for this user.'], 404);
        }

        $donor = $user->donor;

        // 2. Load all donations for the donor, eager loading certificate and currency
        $donations = $donor->donations()
            ->with(['certificate', 'currency', 'qr']) 
            ->whereIn('status', ['succeeded', 'paid']) // Only successful donations
            ->orderBy('date', 'desc')
            ->get();

        // 3. Return the transformed collection
        return DonationResource::collection($donations);
    }
}
