<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthTypeUser extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
