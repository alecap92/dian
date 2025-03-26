<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypeLawDeduction extends Model
{
    protected $fillable = [
        'name', 'code', 'percentaje',
    ];
}
