<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarAvailabilityRequest;
use App\Interfaces\CarAvailabilityServiceInterface;
use Illuminate\Http\JsonResponse;

class CarController extends Controller
{
    protected CarAvailabilityServiceInterface $availabilityService;

    public function __construct(CarAvailabilityServiceInterface $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function index(CarAvailabilityRequest $request): JsonResponse
    {
        $data = $this->availabilityService->getAvailability(
            $request->validated('start_date'),
            $request->validated('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}