<?php

namespace App\Http\Requests\Api;

use App\Rules\ResolutionSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->count();
        if($this->count_resolutions < 2)
            $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->first();
        else{
            $this->count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->count();
            if($this->count_resolutions < 2)
                $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->first();
            else
                $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->where('prefix', $this->prefix)->first();
            }

        return [
            // Adicionales Facturador
            'ivaresponsable' => 'nullable|string',
            'nombretipodocid' => 'nullable|string',
            'tarifaica' => 'nullable|string',
            'actividadeconomica' => 'nullable|string',

            // Datos del Establecimiento
            'establishment_name' => 'nullable|string',
            'establishment_address' => 'nullable|string',
            'establishment_phone' => 'nullable|numeric|digits_between:7,10',
            'establishment_municipality' => 'nullable|exists:municipalities,id',
            'establishment_email' => 'nullable|string|email',
            'establishment_logo' => 'nullable|string',

            // Prefijo del Nombre del AttachedDocument
            'atacheddocument_name_prefix' => 'nullable|string',

            // Regimen SEZE
            'seze' => 'nullable|string',  // Cadena indicando año de inicio regimen SEZE y año de formacion de sociedad separados por guion Ejemplo 2021-2017

            // Nota Encabezado y pie de pagina
            'foot_note' => 'nullable|string',
            'head_note' => 'nullable|string',

            // Desactivar texto de confirmacion de pago
            'disable_confirmation_text' => 'nullable|boolean',

            // Enviar Correo al Adquiriente
            'sendmail' => 'nullable|boolean',
            'sendmailtome' => 'nullable|boolean',

            // HTML string body email
            'html_header' => 'nullable|string',
            'html_body' => 'nullable|string',
            'html_buttons' => 'nullable|string',
            'html_footer' => 'nullable|string',

            // Lista de correos a enviar copia
            'email_cc_list' => 'nullable|array',
            'email_cc_list.*.email' => 'nullable|required_with:email_cc_list,|string|email',

            // Document
            'type_document_id' => [
                'required',
                'in:9',
                'exists:type_documents,id',
                new ResolutionSetting(),
            ],

            // Novelty

            'novelty' => 'nullable|array',
            'novelty.novelty' => 'nullable|required_with:novelty|boolean',
            'novelty.uuidnov' => 'nullable|required_if:novelty.novelty,true|string',

            // Period
            'period' => 'required|array',
            'period.admision_date' => 'required|date_format:Y-m-d',
            'period.retirement_date' => 'nullable|date_format:Y-m-d',
            'period.settlement_start_date' => 'required|date_format:Y-m-d',
            'period.settlement_end_date' => 'required|date_format:Y-m-d',
            'period.worked_time' => 'required|numeric',
            'period.issue_date' => 'required|date_format:Y-m-d',

            // Secuence number
            'worker_code' => 'nullable|string',

            'resolution_number' => Rule::requiredIf(function(){
                if(auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->count() >= 2)
                  return true;
                else
                  return false;
            }),

            'prefix' => Rule::requiredIf(function(){
                if(auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution_number', $this->resolution_number)->count() >= 2)
                    return true;
                else
                    return false;
            }),

            'consecutive' => 'required|integer|between:'.optional($this->resolution)->from.','.optional($this->resolution)->to,

            // General Information
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'notes' => 'nullable|string',

            // Worker
            'worker' => 'required|array',
            'worker.type_worker_id' => 'required|exists:type_workers,id',
            'worker.sub_type_worker_id' => 'required|exists:sub_type_workers,id',
            'worker.payroll_type_document_identification_id' => 'required|exists:payroll_type_document_identifications,id',
            'worker.municipality_id' => 'required|exists:municipalities,id',
            'worker.type_contract_id' => 'required|exists:type_contracts,id',
            'worker.high_risk_pension' => 'required|boolean',
            'worker.identification_number' => 'required|alpha_num|between:1,15',
            'worker.surname' => 'required|string',
            'worker.second_surname' => 'nullable|string',
            'worker.first_name' => 'required|string',
            'worker.middle_name' => 'nullable|string',
            'worker.address' => 'required|string',
            'worker.email' => 'nullable|string|email',
            'worker.integral_salarary' => 'required|boolean',
            'worker.salary' => 'required|numeric',
            'worker.worker_code' => 'nullable|string',

            // SMTP Server Parameters
            'smtp_parameters' => 'nullable|array',
            'smtp_parameters.host' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.port' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.username' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.password' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.encryption' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_address' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_name' => 'nullable|required_with:smtp_parameters|string',

            // Payment
            'payment' => 'required|array',
            'payment.payment_method_id' => 'required|exists:payment_methods,id',
            'payment.bank_name' => 'nullable|required_if:payment.payment_method_id,2,3,4,5,6,7,21,22,30,31,42,45,46,47,|string',
            'payment.account_type' => 'nullable|required_if:payment.payment_method_id,2,3,4,5,6,7,21,22,30,31,42,45,46,47,|string',
            'payment.account_number' => 'nullable|required_if:payment.payment_method_id,2,3,4,5,6,7,21,22,30,31,42,45,46,47,|string',

            // Payment Dates
            'payment_dates' => 'required|array',
            'payment_dates.*.payment_date' => 'required|date_format:Y-m-d',

            // Accrued
            'accrued' => 'required|array',
            'accrued.worked_days' => 'required|numeric|digits_between:1,2',
            'accrued.salary' => 'required|numeric',
            'accrued.transportation_allowance' => 'nullable|numeric',
            'accrued.salary_viatics' => 'nullable|numeric',
            'accrued.non_salary_viatics' => 'nullable|numeric',

            'accrued.*.HEDs' => 'nullable|array',
            'accrued.*.HEDs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDs.*.quantity' => 'required|numeric',
            'accrued.*.HEDs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HEDs.*.payment' => 'required|numeric',

            'accrued.*.HENs' => 'nullable|array',
            'accrued.*.HENs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENs.*.quantity' => 'required|numeric',
            'accrued.*.HENs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HENs.*.payment' => 'required|numeric',

            'accrued.*.HRNs' => 'nullable|array',
            'accrued.*.HRNs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNs.*.quantity' => 'required|numeric',
            'accrued.*.HRNs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HRNs.*.payment' => 'required|numeric',

            'accrued.*.HEDDFs' => 'nullable|array',
            'accrued.*.HEDDFs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDDFs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDDFs.*.quantity' => 'required|numeric',
            'accrued.*.HEDDFs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HEDDFs.*.payment' => 'required|numeric',

            'accrued.*.HRDDFs' => 'nullable|array',
            'accrued.*.HRDDFs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRDDFs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRDDFs.*.quantity' => 'required|numeric',
            'accrued.*.HRDDFs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HRDDFs.*.payment' => 'required|numeric',

            'accrued.*.HENDFs' => 'nullable|array',
            'accrued.*.HENDFs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENDFs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENDFs.*.quantity' => 'required|numeric',
            'accrued.*.HENDFs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HENDFs.*.payment' => 'required|numeric',

            'accrued.*.HRNDFs' => 'nullable|array',
            'accrued.*.HRNDFs.*.start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNDFs.*.end_time' => 'required|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNDFs.*.quantity' => 'required|numeric',
            'accrued.*.HRNDFs.*.percentage' => 'required|exists:type_overtime_surcharge,id',
            'accrued.*.HRNDFs.*.payment' => 'required|numeric',

            'accrued.*.common_vacation' => 'nullable|array',
            'accrued.*.common_vacation.*.start_date' => 'required|date_format:Y-m-d',
            'accrued.*.common_vacation.*.end_date' => 'required|date_format:Y-m-d',
            'accrued.*.common_vacation.*.quantity' => 'required|numeric',
            'accrued.*.common_vacation.*.payment' => 'required|numeric',

            'accrued.*.paid_vacation' => 'nullable|array',
            'accrued.*.paid_vacation.*.quantity' => 'required|numeric',
            'accrued.*.paid_vacation.*.payment' => 'required|numeric',

            'accrued.*.service_bonus' => 'nullable|array',
            'accrued.*.service_bonus.*.quantity' => 'required|numeric',
            'accrued.*.service_bonus.*.payment' => 'required|numeric',
            'accrued.*.service_bonus.*.paymentNS' => 'nullable|numeric',

            'accrued.*.severance' => 'nullable|array',
            'accrued.*.severance.*.payment' => 'required|numeric',
            'accrued.*.severance.*.percentage' => 'required|numeric',
            'accrued.*.severance.*.interest_payment' => 'required|numeric',

            'accrued.*.work_disabilities' => 'nullable|array',
            'accrued.*.work_disabilities.*.start_date' => 'required|date_format:Y-m-d',
            'accrued.*.work_disabilities.*.end_date' => 'required|date_format:Y-m-d',
            'accrued.*.work_disabilities.*.quantity' => 'required|numeric',
            'accrued.*.work_disabilities.*.type' => 'required|exists:type_disabilities,id',
            'accrued.*.work_disabilities.*.payment' => 'required|numeric',

            'accrued.*.maternity_leave' => 'nullable|array',
            'accrued.*.maternity_leave.*.start_date' => 'required|date_format:Y-m-d',
            'accrued.*.maternity_leave.*.end_date' => 'required|date_format:Y-m-d',
            'accrued.*.maternity_leave.*.quantity' => 'required|numeric',
            'accrued.*.maternity_leave.*.payment' => 'required|numeric',

            'accrued.*.paid_leave' => 'nullable|array',
            'accrued.*.paid_leave.*.start_date' => 'required|date_format:Y-m-d',
            'accrued.*.paid_leave.*.end_date' => 'required|date_format:Y-m-d',
            'accrued.*.paid_leave.*.quantity' => 'required|numeric',
            'accrued.*.paid_leave.*.payment' => 'required|numeric',

            'accrued.*.non_paid_leave' => 'nullable|array',
            'accrued.*.non_paid_leave.*.start_date' => 'required|date_format:Y-m-d',
            'accrued.*.non_paid_leave.*.end_date' => 'required|date_format:Y-m-d',
            'accrued.*.non_paid_leave.*.quantity' => 'required|numeric',

            'accrued.*.bonuses' => 'nullable|array',
            'accrued.*.bonuses.*.salary_bonus' => 'nullable|numeric',
            'accrued.*.bonuses.*.non_salary_bonus' => 'nullable|numeric',

            'accrued.*.aid' => 'nullable|array',
            'accrued.*.aid.*.salary_assistance' => 'nullable|numeric',
            'accrued.*.aid.*.non_salary_assistance' => 'nullable|numeric',

            'accrued.*.legal_strike' => 'nullable|array',
            'accrued.*.legal_strike.*.start_date' => 'required|date_format:Y-m-d',
            'accrued.*.legal_strike.*.end_date' => 'required|date_format:Y-m-d',
            'accrued.*.legal_strike.*.quantity' => 'required|numeric',

            'accrued.*.other_concepts' => 'nullable|array',
            'accrued.*.other_concepts.*.salary_concept' => 'nullable|numeric',
            'accrued.*.other_concepts.*.non_salary_concept' => 'nullable|numeric',
            'accrued.*.other_concepts.*.description_concept' => 'required|string',

            'accrued.*.compensations' => 'nullable|array',
            'accrued.*.compensations.*.ordinary_compensation' => 'required|numeric',
            'accrued.*.compensations.*.extraordinary_compensation' => 'required|numeric',

            'accrued.*.epctv_bonuses' => 'nullable|array',
            'accrued.*.epctv_bonuses.*.paymentS' => 'nullable|numeric',
            'accrued.*.epctv_bonuses.*.paymentNS' => 'nullable|numeric',
            'accrued.*.epctv_bonuses.*.salary_food_payment' => 'nullable|numeric',
            'accrued.*.epctv_bonuses.*.non_salary_food_payment' => 'nullable|numeric',

            'accrued.*.commissions' => 'nullable|array',
            'accrued.*.commissions.*.commission' => 'required|numeric',

            'accrued.*.third_party_payments' => 'nullable|array',
            'accrued.*.third_party_payments.*.third_party_payment' => 'required|numeric',

            'accrued.*.advances' => 'nullable|array',
            'accrued.*.advances.*.advance' => 'nullable|numeric',

            'accrued.endowment' => 'nullable|numeric',
            'accrued.sustenance_support' => 'nullable|numeric',
            'accrued.telecommuting' => 'nullable|numeric',
            'accrued.withdrawal_bonus' => 'nullable|numeric',
            'accrued.compensation' => 'nullable|numeric',
            'accrued.refund' => 'nullable|numeric',

            'accrued.accrued_total' => 'required|numeric',

            // Deductions
            'deductions' => 'required|array',
            'deductions.eps_type_law_deductions_id' => 'required|exists:type_law_deductions,id',
            'deductions.eps_deduction' => 'required|numeric',
            'deductions.pension_type_law_deductions_id' => 'required|exists:type_law_deductions,id',
            'deductions.pension_deduction' => 'required|numeric',
            'deductions.fondossp_type_law_deductions_id' => 'nullable|exists:type_law_deductions,id',
            'deductions.fondosp_deduction_SP' => 'nullable|numeric',
            'deductions.fondossp_sub_type_law_deductions_id' => 'nullable|exists:type_law_deductions,id',
            'deductions.fondosp_deduction_sub' => 'nullable|numeric',

            'deductions.*.labor_union' => 'nullable|array',
            'deductions.*.labor_union.*.percentage' => 'required|numeric',
            'deductions.*.labor_union.*.deduction' => 'required|numeric',

            'deductions.*.sanctions' => 'nullable|array',
            'deductions.*.sanctions.*.public_sanction' => 'nullable|numeric',
            'deductions.*.sanctions.*.private_sanction' => 'nullable|numeric',

            'deductions.*.orders' => 'nullable|array',
            'deductions.*.orders.*.description' => 'required|numeric',
            'deductions.*.orders.*.deduction' => 'required|numeric',

            'deductions.*.third_party_payments' => 'nullable|array',
            'deductions.*.third_party_payments.*.third_party_payment' => 'required|numeric',

            'deductions.*.advances' => 'nullable|array',
            'deductions.*.advances.*.advance' => 'required|numeric',

            'deductions.*.other_deductions' => 'nullable|array',
            'deductions.*.other_deductions.*.other_deduction' => 'required|numeric',

            'deductions.voluntary_pension' => 'nullable|numeric',
            'deductions.withholding_at_source' => 'nullable|numeric',
            'deductions.afc' => 'nullable|numeric',
            'deductions.cooperative' => 'nullable|numeric',
            'deductions.tax_liens' => 'nullable|numeric',
            'deductions.supplementary_plan' => 'nullable|numeric',
            'deductions.education' => 'nullable|numeric',
            'deductions.refund' => 'nullable|numeric',
            'deductions.debt' => 'nullable|numeric',

            'deductions.deductions_total' => 'required|numeric',
        ];
    }
}
