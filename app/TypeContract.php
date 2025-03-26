<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypeContract extends Model
{
    protected $fillable = [
        'name', 'code', 'description',
    ];
}
