<?php

namespace App\Services;

use App\Models\Car;
use App\Interfaces\CarAvailabilityServiceInterface;
use Carbon\Carbon;

class CarAvailabilityService implements CarAvailabilityServiceInterface
{
    public function getAvailability(string $startDate, string $endDate): array
    {
        $cars = Car::where('company_id', 1)
            ->where('status', 1)
            ->where('is_deleted', '!=', 1)
            ->orderBy('car_id', 'asc')
            ->with(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 1)
                      ->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate)
                      ->orderBy('start_date', 'asc');
            }])->get();

        $result = [];

        foreach ($cars as $car) {
            $result[] = [
                'id' => $car->car_id,
                'name' => $car->name ?? 'Unknown',
                'year' => $car->year ?? null,
                'free_days' => 0,
            ];
        }

        return $result;
    }
}