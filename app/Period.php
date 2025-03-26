<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admision_date', 'retirement_date', 'settlement_start_date', 'settlement_end_date', 'worked_time', 'issue_date',
    ];
}
