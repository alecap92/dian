<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'eps_type_law_deductions_id', 'eps_base_value', 'eps_deduction', 'pension_type_law_deductions_id', 'pension_base_value',
        'pension_deduction', 'fondossp_type_law_deductions_id', 'fondosp_deduction_SP', 'fondossp_sub_type_law_deductions_id',
        'fondosp_deduction_sub', 'deductions_total', 'labor_union', 'sanctions', 'orders', 'voluntary_pension', 'withholding_at_source',
        'afc', 'cooperative', 'tax_liens', 'supplementary_plan', 'education', 'refund', 'debt', 'third_party_payments', 'advances', 'other_deductions',
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->eps_type_law_deductions = $this->type_law_deduction($this->attributes['eps_type_law_deductions_id']);
        $this->pension_type_law_deductions = $this->type_law_deduction($this->attributes['pension_type_law_deductions_id']);
        if(isset($this->attributes['fondossp_type_law_deductions_id']))
            $this->fondossp_type_law_deductions = $this->type_law_deduction($this->attributes['fondossp_type_law_deductions_id']);
        if(isset($this->attributes['fondossp_sub_type_law_deductions_id']))
            $this->fondossp_sub_type_law_deductions = $this->type_law_deduction($this->attributes['fondossp_sub_type_law_deductions_id']);
        if(isset($this->attributes['labor_union']))
            $this->labor_union = $this->setGeneralItem($this->attributes['labor_union']);
        if(isset($this->attributes['sanctions']))
            $this->sanctions = $this->setGeneralItem($this->attributes['sanctions']);
        if(isset($this->attributes['orders']))
            $this->orders = $this->setGeneralItem($this->attributes['orders']);
        if(isset($this->attributes['third_party_payments']))
            $this->third_party_payments = $this->setGeneralItem($this->attributes['third_party_payments']);
        if(isset($this->attributes['advances']))
            $this->advances = $this->setGeneralItem($this->attributes['advances']);
        if(isset($this->attributes['other_deductions']))
            $this->other_deductions = $this->setGeneralItem($this->attributes['other_deductions']);
    }

    /**
    * Get the type law deduction eps belongs to
    */
    public function type_law_deduction($id) {
        return TypeLawDeduction::where('id', $id)->firstOrfail();
    }

    /**
     * Set the accrued General Items.
     *
     * @param string $value
     */
    public function setGeneralItem(array $data = [])
    {
        $GeneralItems = collect();

        foreach ($data as $value) {
            $GeneralItems->push(new GeneralItem($value));
        }
        return $GeneralItems;
    }
}
