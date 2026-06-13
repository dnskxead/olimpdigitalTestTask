<?php

namespace App\Services;

use App\Models\Car;
use App\Interfaces\CarAvailabilityServiceInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CarAvailabilityService implements CarAvailabilityServiceInterface
{
    public function getAvailability(string $startDate, string $endDate): array
    {
        $periodStart = Carbon::parse($startDate)->startOfDay();
        $periodEnd = Carbon::parse($endDate)->endOfDay();
        $cars = Car::where('company_id', 1)
            ->where('status', 1)
            ->where('is_deleted', '!=', 1)
            ->orderBy('car_id', 'asc')
            ->with(['bookings' => function ($query) use ($periodStart, $periodEnd) {
                $query->where('status', 1)
                      ->where('start_date', '<=', $periodEnd->toDateTimeString())
                      ->where('end_date', '>=', $periodStart->toDateTimeString())
                      ->orderBy('start_date', 'asc');
            }])
            ->get();

        $result = [];
        foreach ($cars as $car) {
            $freeDaysCount = $this->calculateFreeDaysCount($car, $periodStart, $periodEnd);
            $totalDays = $periodStart->diffInDays($periodEnd) + 1;

            $result[] = [
                'id' => $car->car_id,
                'name' => $car->name ?? 'Unknown',
                'year' => $car->year ?? null,
                'number' => $car->number ?? null,
                'free' => $freeDaysCount,
                'busy' => $totalDays - $freeDaysCount,
                'all' => $totalDays,
            ];
        }

        return $result;
    }

    private function calculateFreeDaysCount(Car $car, Carbon $periodStart, Carbon $periodEnd): int
    {
        $freeDays = 0;
        $period = CarbonPeriod::create($periodStart, $periodEnd);

        foreach ($period as $date) {
            //09:00 - 21:00
            $dayStart = $date->copy()->setTime(9, 0);
            $dayEnd = $date->copy()->setTime(21, 0);
            $freeIntervals = [
                ['start' => $dayStart, 'end' => $dayEnd]
            ];

            foreach ($car->bookings as $booking) {
                $bStart = Carbon::parse($booking->start_date)->subHours(2);
                $bEnd = Carbon::parse($booking->end_date)->subHours(2);

                // if time is not 09:00 - 21:00, skip
                if ($bEnd->lte($dayStart) || $bStart->gte($dayEnd)) {
                    continue;
                }

                $newIntervals = [];
                foreach ($freeIntervals as $interval) {
                    if ($bStart->gte($interval['end']) || $bEnd->lte($interval['start'])) {
                        $newIntervals[] = $interval; 
                    } else {
                        if ($interval['start']->lt($bStart)) {
                            $newIntervals[] = ['start' => $interval['start'], 'end' => $bStart];
                        }
                        if ($interval['end']->gt($bEnd)) {
                            $newIntervals[] = ['start' => $bEnd, 'end' => $interval['end']];
                        }
                    }
                }
                $freeIntervals = $newIntervals;
            }
            $isDayFree = false;
            foreach ($freeIntervals as $interval) {
                if ($interval['start']->diffInMinutes($interval['end']) >= 9 * 60) {
                    $isDayFree = true;
                    break;
                }
            }
            if ($isDayFree) {
                $freeDays++;
            }
        }

        return $freeDays;
    }
}