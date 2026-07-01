<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SaasMetricsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, SaasMetricsService $metricsService)
    {
        $user = $request->user();

        if (! $user || ! $user->is_platform_user() || ! $user->is_owner) {
            abort(403, 'Accès réservé à l’administration plateforme.');
        }

        $metrics = $metricsService->getDashboardMetrics();

        return view('back.dashboard.admin', compact('metrics'));
    }
}
