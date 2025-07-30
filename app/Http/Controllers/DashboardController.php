<?php

namespace App\Http\Controllers;

use App\Contracts\Services\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function getStats(
        DashboardServiceInterface $dashboardService
    ): JsonResponse
    {
        $data = $dashboardService->getStats();
        return new JsonResponse($data);
    }
}
