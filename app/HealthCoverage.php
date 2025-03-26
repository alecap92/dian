<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthCoverage extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
