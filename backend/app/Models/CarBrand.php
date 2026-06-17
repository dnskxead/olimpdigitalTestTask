<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarBrand extends Model
{
    protected $table = 'rc_cars_brands';

    protected $primaryKey = 'car_brand_id';

    public function translations(): HasMany
    {
        return $this->hasMany(CarBrandTranslation::class, 'car_brand_id', 'car_brand_id');
    }
}
