<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarTranslation extends Model
{
    protected $table = 'rc_cars_translations';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
