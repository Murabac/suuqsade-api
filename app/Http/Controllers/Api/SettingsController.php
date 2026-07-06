<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function public(SettingsService $settings): JsonResponse
    {
        return response()->json($settings->publicSettings());
    }
}
