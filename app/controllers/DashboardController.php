<?php

namespace App\Controllers;

use App\Services\ApiService;

class DashboardController
{
    public function index(): void
    {
        $api = new ApiService();
        $response = $api->get('dashboard');
        $dashboardData = $response['data'] ?? [];

        include __DIR__ . '/../views/dashboard/index.php';
    }
}
