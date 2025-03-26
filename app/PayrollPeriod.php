<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
