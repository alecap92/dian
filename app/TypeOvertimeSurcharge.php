<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypeOvertimeSurcharge extends Model
{
    protected $fillable = [
        'name', 'code', 'percentaje',
    ];
}
