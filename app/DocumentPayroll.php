<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use App\TypeDocument;

class DocumentPayroll extends Model
{
    use SoftDeletes;

    protected $with = ['type_document'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['identification_number','state_document_id', 'type_document_id', 'prefix', 'consecutive', 'xml', 'pdf', 'cune', 'employee_id', 'date_issue', 'accrued_total', 'deductions_total', 'total_payroll'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
    * Get the type document belongs to
    */
    public function type_document() {
        return $this->belongsTo(TypeDocument::class);
    }


    /**
     * Filtros busqueda nomina
     * Usado en:
     * SendEmailController - sendEmailDocumentPayroll
     * 
     * @param $query
     * @param $company_identification_number
     * @param $prefix
     * @param $number
    */
    public function scopeWhereExistRecord($query, $company_identification_number, $prefix, $consecutive)
    {
        return $query->where('identification_number', $company_identification_number)
                    ->where('prefix', $prefix)
                    ->where('consecutive', $consecutive)
                    ->where('state_document_id', 1);
    }

}
