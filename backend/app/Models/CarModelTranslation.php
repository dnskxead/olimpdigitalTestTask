<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModelTranslation extends Model
{
    protected $table = 'rc_cars_models_translations';
    protected $primaryKey = 'id';
    public $timestamps = false;
}