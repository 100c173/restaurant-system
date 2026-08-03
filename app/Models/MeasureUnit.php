<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeasureUnit extends Model
{
    protected $connection = 'central' ;
    protected $fillable = [
        'usda_id',
        'name_en',
        'name_ar',
        'notes'
    ];
}
