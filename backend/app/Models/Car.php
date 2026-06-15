<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $table = 'rc_cars';
    protected $primaryKey = 'car_id';

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'car_model_id', 'car_model_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'car_id', 'car_id');
    }
}