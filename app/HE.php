<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TypeOvertimeSurcharge;

class HE extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_time', 'end_time', 'quantity', 'percentage', 'payment',
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->type_overtime_surcharge = $this->type_overtime_surcharge();
    }

    /**
    * Get the type overtime surcharge HED belongs to
    */
    public function type_overtime_surcharge() {
        return TypeOvertimeSurcharge::where('id', $this->percentage)->firstOrfail();
    }

}
