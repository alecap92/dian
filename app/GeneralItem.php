<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TypeDisability;

class GeneralItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_date', 'end_date', 'quantity', 'percentage', 'payment', 'paymentNS', 'interest_payment', 'type', 'salary_bonus',
        'non_salary_bonus', 'salary_assistance', 'non_salary_assistance', 'salary_concept', 'non_salary_concept', 'description_concept',
        'paymentS', 'paymentNS', 'salary_food_payment', 'non_salary_food_payment', 'commission', 'third_party_payment', 'advance',
        'ordinary_compensation', 'extraordinary_compensation', 'deduction', 'public_sanction', 'private_sanction', 'description',
        'other_deduction',
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        if(isset($this->attributes['type']))
            $this->type_disability = $this->type_disability();
    }

    /**
    * Get the type disability belongs to
    */
    public function type_disability() {
        return TypeDisability::where('id', $this->type)->firstOrfail();
    }

}
