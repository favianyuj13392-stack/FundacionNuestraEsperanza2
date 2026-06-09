<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransparencyService;
use Illuminate\Http\Request;

class TransparencyController extends Controller
{
    protected $transparencyService;

    public function __construct(TransparencyService $transparencyService)
    {
        $this->transparencyService = $transparencyService;
    }

    public function index()
    {
        $campaigns = $this->transparencyService->getActiveCampaigns();
        return response()->json(['success' => true, 'data' => $campaigns]);
    }

    public function show($slug)
    {
        $data = $this->transparencyService->getCampaignDetails($slug);
        return response()->json(['success' => true, 'data' => $data]);
    }
}
