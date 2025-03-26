<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accrued extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'worked_days', 'salary', 'transportation_allowance', 'salary_viatics', 'non_salary_viatics', 'accrued_total', 'HEDs',
        'HENs', 'HRNs', 'HEDDFs', 'HRDDFs', 'HENDFs', 'HRNDFs', 'common_vacation', 'paid_vacation', 'service_bonus', 'severance',
        'work_disabilities', 'maternity_leave', 'paid_leave', 'non_paid_leave', 'bonuses', 'aid', 'legal_strike', 'other_concepts',
        'compensations', 'epctv_bonuses', 'commissions', 'third_party_payments', 'advances', 'endowment', 'sustenance_support',
        'telecommuting', 'withdrawal_bonus', 'compensation',
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        if(isset($this->attributes['HEDs']))
            $this->HEDs = $this->setHEs($this->attributes['HEDs']);
        if(isset($this->attributes['HENs']))
            $this->HENs = $this->setHEs($this->attributes['HENs']);
        if(isset($this->attributes['HRNs']))
            $this->HRNs = $this->setHEs($this->attributes['HRNs']);
        if(isset($this->attributes['HEDDFs']))
            $this->HEDDFs = $this->setHEs($this->attributes['HEDDFs']);
        if(isset($this->attributes['HRDDFs']))
            $this->HRDDFs = $this->setHEs($this->attributes['HRDDFs']);
        if(isset($this->attributes['HENDFs']))
            $this->HENDFs = $this->setHEs($this->attributes['HENDFs']);
        if(isset($this->attributes['HRNDFs']))
            $this->HRNDFs = $this->setHEs($this->attributes['HRNDFs']);
        if(isset($this->attributes['common_vacation']))
            $this->common_vacation = $this->setGeneralItem($this->attributes['common_vacation']);
        if(isset($this->attributes['paid_vacation']))
            $this->paid_vacation = $this->setGeneralItem($this->attributes['paid_vacation']);
        if(isset($this->attributes['service_bonus']))
            $this->service_bonus = $this->setGeneralItem($this->attributes['service_bonus']);
        if(isset($this->attributes['severance']))
            $this->severance = $this->setGeneralItem($this->attributes['severance']);
        if(isset($this->attributes['work_disabilities']))
            $this->work_disabilities = $this->setGeneralItem($this->attributes['work_disabilities']);
        if(isset($this->attributes['maternity_leave']))
            $this->maternity_leave = $this->setGeneralItem($this->attributes['maternity_leave']);
        if(isset($this->attributes['paid_leave']))
            $this->paid_leave = $this->setGeneralItem($this->attributes['paid_leave']);
        if(isset($this->attributes['non_paid_leave']))
            $this->non_paid_leave = $this->setGeneralItem($this->attributes['non_paid_leave']);
        if(isset($this->attributes['bonuses']))
            $this->bonuses = $this->setGeneralItem($this->attributes['bonuses']);
        if(isset($this->attributes['aid']))
            $this->aid = $this->setGeneralItem($this->attributes['aid']);
        if(isset($this->attributes['legal_strike']))
            $this->legal_strike = $this->setGeneralItem($this->attributes['legal_strike']);
        if(isset($this->attributes['other_concepts']))
            $this->other_concepts = $this->setGeneralItem($this->attributes['other_concepts']);
        if(isset($this->attributes['compensations']))
            $this->compensations = $this->setGeneralItem($this->attributes['compensations']);
        if(isset($this->attributes['epctv_bonuses']))
            $this->epctv_bonuses = $this->setGeneralItem($this->attributes['epctv_bonuses']);
        if(isset($this->attributes['commissions']))
            $this->commissions = $this->setGeneralItem($this->attributes['commissions']);
        if(isset($this->attributes['third_party_payments']))
            $this->third_party_payments = $this->setGeneralItem($this->attributes['third_party_payments']);
        if(isset($this->attributes['advances']))
            $this->advances = $this->setGeneralItem($this->attributes['advances']);
    }

    /**
     * Set the accrued HEs.
     *
     * @param string $value
     */
    public function setHEs(array $data = [])
    {
        $HEs = collect();

        foreach ($data as $value) {
            $HEs->push(new HE($value));
        }
        return $HEs;
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
