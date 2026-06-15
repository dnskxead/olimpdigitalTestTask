<?php

namespace App\Services;

use App\Models\Car;
use App\Interfaces\CarAvailabilityServiceInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

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
            }, 'carModel.translations'])
            ->get();

        $result = [];
        foreach ($cars as $car) {
            $freeDaysCount = $this->calculateFreeDaysCount($car, $periodStart, $periodEnd);
            $totalDays = max(1, $periodStart->diffInDays($periodEnd) + 1);
            $displayName = $this->resolveCarName($car->carModel?->translations ?? collect());
            $serviceCount = $car->bookings->where('source', 'car-service')->count();

            $result[] = [
                'id'        => $car->car_id,
                'name'      => $displayName,
                'year'      => $car->attribute_year ? (int) $car->attribute_year : null,
                'number'    => $car->registration_number ?? null,
                'body_type' => $car->car_body_id ? (string) $car->car_body_id : null,
                'service'   => $serviceCount,
                'free'      => (int) $freeDaysCount,
                'busy'      => (int) max(0, $totalDays - $freeDaysCount),
                'all'       => (int) $totalDays,
            ];
        }

        return $result;
    }

    private function resolveCarName(Collection $translations): string
    {
        $preferredTranslation = $translations->firstWhere('lang', 'en')
            ?? $translations->firstWhere('name');

        return $preferredTranslation?->name
            ?? $translations->first()?->name
            ?? 'Unknown';
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
                $bStart = Carbon::parse($booking->start_date)->startOfMinute();
                $bEnd = Carbon::parse($booking->end_date)->startOfMinute();

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