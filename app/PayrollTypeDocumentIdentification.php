<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayrollTypeDocumentIdentification extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
