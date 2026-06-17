<?php

namespace App\Services;

use App\Interfaces\CarAvailabilityServiceInterface;
use App\Models\Car;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class CarAvailabilityService implements CarAvailabilityServiceInterface
{
    private const DB_TIMEZONE = 'UTC';

    private const AVAILABLE_FROM_HOUR = 9;

    private const AVAILABLE_TO_HOUR = 21;

    private const MIN_FREE_MINUTES = 9 * 60;

    private const BODY_TYPES = [
        4 => 'sedan',
        6 => 'coupe',
        7 => 'convertible',
        11 => 'suv',
    ];

    public function getAvailability(string $startDate, string $endDate): array
    {
        $periodStart = Carbon::parse($startDate, $this->appTimezone())->startOfDay();
        $periodEnd = Carbon::parse($endDate, $this->appTimezone())->endOfDay();
        $queryStart = $periodStart->copy()->setTimezone(self::DB_TIMEZONE);
        $queryEnd = $periodEnd->copy()->setTimezone(self::DB_TIMEZONE);

        $cars = Car::where('company_id', 1)
            ->where('status', 1)
            ->where('is_deleted', '!=', 1)
            ->orderBy('car_id', 'asc')
            ->with(['bookings' => function ($query) use ($queryStart, $queryEnd) {
                $query->where('status', 1)
                    ->where('start_date', '<=', $queryEnd->toDateTimeString())
                    ->where('end_date', '>=', $queryStart->toDateTimeString())
                    ->orderBy('start_date', 'asc');
            }, 'translations', 'carModel.translations', 'carModel.brand.translations'])
            ->get();

        $result = [];
        foreach ($cars as $car) {
            $counts = $this->calculateAvailabilityCounts($car, $periodStart, $periodEnd);
            $totalDays = max(1, $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1);

            $result[] = [
                'id' => $car->car_id,
                'name' => $this->resolveCarName($car),
                'year' => $car->attribute_year ? (int) $car->attribute_year : null,
                'color' => $this->resolveColor($car->translations ?? collect()),
                'brand' => $this->resolveBrand($car),
                'number' => $car->registration_number ?? null,
                'body_type' => $this->resolveBodyType($car->car_body_id),
                'create' => $this->formatCreatedDate($car->created_at),
                'car_types' => $this->resolveCarTypes($car),
                'free' => $counts['free'],
                'service' => $counts['service'],
                'busy' => $counts['busy'],
                'all' => (int) $totalDays,
            ];
        }

        return $result;
    }

    private function resolveCarName(Car $car): string
    {
        $carTranslation = $car->translations?->firstWhere('lang', 'en')
            ?? $car->translations?->firstWhere('title');
        if ($carTranslation?->title) {
            return $carTranslation->title;
        }

        $modelTranslations = $car->carModel?->translations ?? collect();
        $modelTranslation = $modelTranslations->firstWhere('lang', 'en')
            ?? $modelTranslations->firstWhere('name');

        return $modelTranslation?->name
            ?? $modelTranslations->first()?->name
            ?? 'Unknown';
    }

    private function resolveColor(Collection $translations): ?string
    {
        $translation = $translations->firstWhere('lang', 'en')
            ?? $translations->firstWhere('attribute_color');

        return $translation?->attribute_color;
    }

    private function resolveBrand(Car $car): ?string
    {
        $brand = $car->carModel?->brand;

        return $brand?->slug
            ?? $brand?->translations?->firstWhere('lang', 'en')?->name
            ?? $brand?->translations?->first()?->name;
    }

    private function resolveCarTypes(Car $car): string
    {
        return collect([$car->price_type])
            ->filter()
            ->implode(', ');
    }

    private function formatCreatedDate($date): ?string
    {
        return $date
            ? Carbon::parse($date, self::DB_TIMEZONE)->setTimezone($this->appTimezone())->toDateString()
            : null;
    }

    private function calculateAvailabilityCounts(Car $car, Carbon $periodStart, Carbon $periodEnd): array
    {
        $counts = [
            'free' => 0,
            'service' => 0,
            'busy' => 0,
        ];
        $period = CarbonPeriod::create($periodStart->copy()->startOfDay(), $periodEnd->copy()->startOfDay());

        foreach ($period as $date) {
            $dayStart = $date->copy()->setTime(self::AVAILABLE_FROM_HOUR, 0);
            $dayEnd = $date->copy()->setTime(self::AVAILABLE_TO_HOUR, 0);
            $bookings = $this->getBookingsForDay($car, $dayStart, $dayEnd);
            $freeIntervals = $this->subtractBookedIntervals([
                ['start' => $dayStart, 'end' => $dayEnd],
            ], $bookings);

            if ($this->hasEnoughFreeTime($freeIntervals)) {
                $counts['free']++;
            } elseif ($bookings->contains(fn (array $booking) => $booking['is_service'])) {
                $counts['service']++;
            } else {
                $counts['busy']++;
            }
        }

        return $counts;
    }

    private function getBookingsForDay(Car $car, Carbon $dayStart, Carbon $dayEnd): Collection
    {
        return $car->bookings
            ->map(fn ($booking) => [
                'start' => $this->bookingDateToAppTimezone($booking->start_date),
                'end' => $this->bookingDateToAppTimezone($booking->end_date),
                'is_service' => $this->isServiceBooking($booking),
            ])
            ->filter(fn (array $booking) => $booking['end']->gt($dayStart) && $booking['start']->lt($dayEnd))
            ->values();
    }

    private function subtractBookedIntervals(array $freeIntervals, Collection $bookings): array
    {
        foreach ($bookings as $booking) {
            $newIntervals = [];
            foreach ($freeIntervals as $interval) {
                if ($booking['start']->gte($interval['end']) || $booking['end']->lte($interval['start'])) {
                    $newIntervals[] = $interval;

                    continue;
                }

                if ($interval['start']->lt($booking['start'])) {
                    $newIntervals[] = ['start' => $interval['start'], 'end' => $booking['start']];
                }

                if ($interval['end']->gt($booking['end'])) {
                    $newIntervals[] = ['start' => $booking['end'], 'end' => $interval['end']];
                }
            }

            $freeIntervals = $newIntervals;
        }

        return $freeIntervals;
    }

    private function hasEnoughFreeTime(array $freeIntervals): bool
    {
        foreach ($freeIntervals as $interval) {
            if ($interval['start']->diffInMinutes($interval['end']) >= self::MIN_FREE_MINUTES) {
                return true;
            }
        }

        return false;
    }

    private function bookingDateToAppTimezone(string $date): Carbon
    {
        return Carbon::parse($date, self::DB_TIMEZONE)->setTimezone($this->appTimezone())->startOfMinute();
    }

    private function appTimezone(): string
    {
        return config('app.timezone', 'Europe/Kyiv');
    }

    private function resolveBodyType(?int $bodyId): ?string
    {
        return $bodyId ? (self::BODY_TYPES[$bodyId] ?? (string) $bodyId) : null;
    }

    private function isServiceBooking($booking): bool
    {
        return $booking->source === 'car-service'
            && strtoupper(trim((string) $booking->other)) !== 'PPF';
    }
}
