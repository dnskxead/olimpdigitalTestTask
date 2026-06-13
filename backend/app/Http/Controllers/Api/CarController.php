<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Interfaces\CarAvailabilityServiceInterface;

class CarController extends Controller
{
    protected CarAvailabilityServiceInterface $availabilityService;
    public function __construct(CarAvailabilityServiceInterface $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function index(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', '2023-01-01 00:00:00');
        $endDate = $request->query('end_date', '2023-01-31 23:59:59');
        $data = $this->availabilityService->getAvailability($startDate, $endDate);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}