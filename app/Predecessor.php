<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Predecessor extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'predecessor_number', 'predecessor_cune', 'predecessor_issue_date',
    ];
}
