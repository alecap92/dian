<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthContractingPaymentMethod extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
