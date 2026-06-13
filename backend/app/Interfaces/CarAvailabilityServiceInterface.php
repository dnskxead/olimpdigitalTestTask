<?php

namespace App\Interfaces;

interface CarAvailabilityServiceInterface
{
    public function getAvailability(string $startDate, string $endDate): array;
}