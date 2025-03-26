<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthTypeOperation extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
