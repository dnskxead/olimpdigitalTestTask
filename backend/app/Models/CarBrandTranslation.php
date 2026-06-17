<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarBrandTranslation extends Model
{
    protected $table = 'rc_cars_brands_translations';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
