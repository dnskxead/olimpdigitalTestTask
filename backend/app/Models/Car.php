<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $table = 'rc_cars';
    protected $primaryKey = 'car_id';

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'car_id', 'car_id');
    }
}