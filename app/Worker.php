<?php

namespace App;

use App\TypeWorker;
use App\SubTypeWorker;
use App\PayrollTypeDocumentIdentification;
use App\Municipality;
use App\Department;
use App\Country;
use App\TypeContract;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_worker_id', 'sub_type_worker_id', 'payroll_type_document_identification_id', 'country_id', 'municipality_id', 'type_contract_id', 'high_risk_pension', 'identification_number', 'surname', 'second_surname', 'first_name', 'middle_name', 'address', 'integral_salarary', 'salary', 'worker_code', 'email',
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->type_worker = $this->type_worker();
        $this->sub_type_worker = $this->sub_type_worker();
        $this->payroll_type_document_identification = $this->payroll_type_document_identification();
        $this->municipality = $this->municipality();
        $this->department = $this->department();
        $this->country = $this->country();
        $this->type_contract = $this->type_contract();
    }

    /**
    * Get the type worker belongs to
    */
    public function type_worker() {
        return TypeWorker::where('id', $this->type_worker_id)->firstOrfail();
    }

    /**
    * Get the sub type worker belongs to
    */
    public function sub_type_worker() {
        return SubTypeWorker::where('id', $this->sub_type_worker_id)->firstOrfail();
    }

    /**
    * Get the sub payroll type document identification worker belongs to
    */
    public function payroll_type_document_identification() {
        return PayrollTypeDocumentIdentification::where('id', $this->payroll_type_document_identification_id)->firstOrfail();
    }

    /**
    * Get the municipality worker belongs to
    */
    public function municipality() {
        return Municipality::where('id', $this->municipality_id)->firstOrfail();
    }

    /**
    * Get the department worker belongs to
    */
    public function department() {
        return Department::where('id', $this->municipality()->department_id)->firstOrfail();
    }

    /**
    * Get the country worker belongs to
    */
    public function country() {
        return Country::where('id', $this->department()->country_id)->firstOrfail();
    }

    /**
    * Get the type contract worker belongs to
    */
    public function type_contract() {
        return TypeContract::where('id', $this->type_contract_id)->firstOrfail();
    }
}
