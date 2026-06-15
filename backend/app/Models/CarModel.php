<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarModel extends Model
{
    protected $table = 'rc_cars_models';
    protected $primaryKey = 'car_model_id';

    public function translations(): HasMany
    {
        return $this->hasMany(CarModelTranslation::class, 'car_model_id', 'car_model_id');
    }
}