<?php

namespace App\Http\Requests\Api;

use App\Rules\ResolutionSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EqDocRequest extends FormRequest
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

            'email_pos_customer' => 'nullable|string|email',

            // Lista de correos a enviar copia
            'email_cc_list' => 'nullable|array',
            'email_cc_list.*.email' => 'nullable|required_with:email_cc_list,|string|email',

            // Documentos en base64 para adjuntar en el attacheddocument
            'annexes' => 'nullable|array',
            'annexes.*.document' => 'nullable|required_with:annexes|string',
            'annexes.*.extension' => 'nullable|required_with:annexes|string',

            // Informacion del fabricante del software
            'software_manufacturer' => 'required|array',
            'software_manufacturer.name' => 'required|string',
            'software_manufacturer.business_name' => 'required|string',
            'software_manufacturer.software_name' => 'required|string',

            // Informacion beneficios del comprador
            'buyer_benefits' => 'nullable|required_if:type_document_id,=,15|array',
            'buyer_benefits.code' => 'nullable|required_with:buyer_benefits|string',
            'buyer_benefits.name' => 'nullable|required_with:buyer_benefits|string',
            'buyer_benefits.points' => 'nullable|required_with:buyer_benefits|string',

            // Informacion de la caja
            'cash_information' => 'nullable|required_if:type_document_id,=,15|array',
            'cash_information.plate_number' => 'nullable|required_with:cash_information|string',
            'cash_information.location' => 'nullable|required_with:cash_information|string',
            'cash_information.cashier' => 'nullable|required_with:cash_information|string',
            'cash_information.cash_type' => 'nullable|required_with:cash_information|string',
            'cash_information.sales_code' => 'nullable|required_with:cash_information|string',
            'cash_information.subtotal' => 'nullable|required_with:cash_information|numeric',

            // Informacion para transporte terrestre
            'transportation_information' => 'nullable|required_if:type_document_id,=,19|array',
            'transportation_information.transport_mode' => 'nullable|required_with:transportation_information|string',
            'transportation_information.plate_number' => 'nullable|required_with:transportation_information|string',
            'transportation_information.transport_mean' => 'nullable|required_with:transportation_information|string',
            'transportation_information.origin_place' => 'nullable|required_with:transportation_information|string',
            'transportation_information.destination_place' => 'nullable|required_with:transportation_information|string',

            // Informacion para boletas de cine
            'show_room_information' => 'nullable|required_if:type_document_id,=,16|array',
            'show_room_information.show_room_name' => 'nullable|required_with:show_room_information|string',
            'show_room_information.address' => 'nullable|required_with:show_room_information|string',
            'show_room_information.exhibition_room' => 'nullable|required_with:show_room_information|string',
            'show_room_information.total_chairs' => 'nullable|required_with:show_room_information|string',
            'show_room_information.name_function' => 'nullable|required_with:show_room_information|string',
            'show_room_information.select_location' => 'nullable|required_with:show_room_information|string',
            'show_room_information.date_function' => 'nullable|date_format:Y-m-d',
            'show_room_information.time_function' => 'nullable|date_format:H:i:s',

            // SPD
            'last_valid_payment_date' => 'required_if:type_document_id,=,24|date_format:Y-m-d',
            'payment_reference' => 'required_if:type_document_id,=,24|string',
            'payment_reference' => 'required_if:type_document_id,=,24|string',
            'stratum_id' => 'required_if:type_document_id,=,24|integer',

            'spd' => 'nullable|required_if:type_document_id,=,24',
            'spd.*.agency_information' => 'required_with:spd|array',
            'spd.*.agency_information.type_spd_id' => 'required_with:spd.*.agency_information|exists:type_spds,id|integer',
            'spd.*.agency_information.contract_number' => 'required_with:spd.*.agency_information|string',
            'spd.*.agency_information.issue_date' => 'required_with:spd.*.agency_information|date_format:Y-m',
            'spd.*.agency_information.note' => 'required_with:spd.*.agency_information|string',
            'spd.*.subscriber_party' => 'required_with:spd|array',
            'spd.*.subscriber_party.party_name' => 'required_with:spd.*.subscriber_party|string',
            'spd.*.subscriber_party.street_name' => 'required_with:spd.*.subscriber_party|string',
            'spd.*.subscriber_party.additional_stret_name' => 'nullable|string',
            'spd.*.subscriber_party.municipality_id' => 'required_with:spd.*.subscriber_party|numeric|exists:municipalities,id',
            'spd.*.subscriber_party.stratum' => 'required_with:spd.*.subscriber_party|string',
            'spd.*.subscriber_party.email' => 'nullable|string|email',
            'spd.*.subscriber_consumption' => 'required_with:spd|array',
            'spd.*.subscriber_consumption.duration_of_the_billing_cycle' => 'required_with:spd.*.subscriber_consumption|numeric',
            'spd.*.subscriber_consumption.consumption_section_note' => 'required_with:spd.*.subscriber_consumption|string',
            'spd.*.subscriber_consumption.total_metered_unit_id' => 'required_with:spd.*.subscriber_consumption|exists:unit_measures,id',
            'spd.*.subscriber_consumption.total_metered_quantity' => 'required_with:spd.*.subscriber_consumption|numeric',
            'spd.*.subscriber_consumption.consumption_payable_amount' => 'required_with:spd.*.subscriber_consumption|numeric',
            'spd.*.subscriber_consumption.consumption_price_quantity' => 'required_with:spd.*.subscriber_consumption|numeric',
            'spd.*.subscriber_consumption.partial_line_extension_amount' => 'required_with:spd.*.subscriber_consumption|numeric',
            'spd.*.subscriber_consumption.descuentos_credito_al_item' => 'nullable|array',
            'spd.*.subscriber_consumption.descuentos_credito_al_item.*.allowance_reason' => 'nullable|required_with:spd.*.subscriber_consumption.descuentos_credito_al_item|string',
            'spd.*.subscriber_consumption.descuentos_credito_al_item.*.amount' => 'nullable|required_with:spd.*.subscriber_consumption.descuentos_credito_al_item|numeric',
            'spd.*.subscriber_consumption.cargos_debito_al_item' => 'nullable|array',
            'spd.*.subscriber_consumption.cargos_debito_al_item.*.charge_reason' => 'nullable|required_with:spd.*.subscriber_consumption.cargos_debito_al_item|string',
            'spd.*.subscriber_consumption.cargos_debito_al_item.*.amount' => 'nullable|required_with:spd.*.subscriber_consumption.cargos_debito_al_item|numeric',
            'spd.*.subscriber_consumption.unstructured_price' => 'nullable|array',
            'spd.*.subscriber_consumption.unstructured_price.price_amount' => 'required_with:spd.*.subscriber_consumption.unstructured_price|numeric',
            'spd.*.subscriber_consumption.unstructured_price.base_quantity' => 'required_with:spd.*.subscriber_consumption.unstructured_price|numeric',
            'spd.*.subscriber_consumption.utiliy_meter' => 'nullable|array',
            'spd.*.subscriber_consumption.utiliy_meter.meter_number' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|string',
            'spd.*.subscriber_consumption.utiliy_meter.previous_meter_reading_date' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|date_format:Y-m-d',
            'spd.*.subscriber_consumption.utiliy_meter.previous_meter_quantity' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|numeric',
            'spd.*.subscriber_consumption.utiliy_meter.latest_meter_reading_date' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|date_format:Y-m-d',
            'spd.*.subscriber_consumption.utiliy_meter.latest_meter_quantity' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|numeric',
            'spd.*.subscriber_consumption.utiliy_meter.meter_reading_method' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|string',
            'spd.*.subscriber_consumption.utiliy_meter.duration_measure' => 'required_with:spd.*.subscriber_consumption.utiliy_meter|numeric',
            'spd.*.subscriber_consumption.consumption_history' => 'nullable|array',
            'spd.*.subscriber_consumption.consumption_history.*.total_invoiced_quantity' => 'nullable|required_with:spd.*.subscriber_consumption.consumption_history|numeric',
            'spd.*.subscriber_consumption.consumption_history.*.start_date' => 'nullable|required_with:spd.*.subscriber_consumption.consumption_history|date_format:Y-m-d',
            'spd.*.subscriber_consumption.consumption_history.*.end_date' => 'nullable|required_with:spd.*.subscriber_consumption.consumption_history|date_format:Y-m-d',
            'spd.*.subscriber_consumption.consumption_history.*.duration_measure' => 'nullable|required_with:spd.*.subscriber_consumption.consumption_history|numeric',
            'spd.*.subscriber_consumption.payment_agreements' => 'nullable|array',
            'spd.*.subscriber_consumption.payment_agreements.*.contract_number' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|string',
            'spd.*.subscriber_consumption.payment_agreements.*.good_service_name' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|string',
            'spd.*.subscriber_consumption.payment_agreements.*.description' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|string',
            'spd.*.subscriber_consumption.payment_agreements.*.fees_to_pay' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',
            'spd.*.subscriber_consumption.payment_agreements.*.paid_fees' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',
            'spd.*.subscriber_consumption.payment_agreements.*.interest_rate' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',
            'spd.*.subscriber_consumption.payment_agreements.*.balance_to_pay' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',
            'spd.*.subscriber_consumption.payment_agreements.*.transaction_description' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|string',
            'spd.*.subscriber_consumption.payment_agreements.*.fee_value_to_pay' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',
            'spd.*.subscriber_consumption.payment_agreements.*.item_credit_discount' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',
            'spd.*.subscriber_consumption.payment_agreements.*.item_debit_charge' => 'nullable|required_with:spd.*.subscriber_consumption.payment_agreements|numeric',

            // HTML string body email
            'html_header' => 'nullable|string',
            'html_body' => 'nullable|string',
            'html_buttons' => 'nullable|string',
            'html_footer' => 'nullable|string',

            // Invoice template name
            'invoice_template' => 'nullable|string',

            // Just query document uuid don't send
            'query_uuid' => 'nullable|boolean',

            // Dynamic field
            'dynamic_field' => 'nullable|array',
            'dynamic_field.name' => 'nullable|required_with:dynamic_field|string',
            'dynamic_field.value' => 'nullable|required_with:dynamic_field|string',
            'dynamic_field.add_to_total' => 'nullable|required_with:dynamic_field|boolean',

            // Other fields for templates
            'sales_assistant' => 'nullable|string',
            'web_site' => 'nullable|string',
            'template_token' => 'nullable|required_with:invoice_template|string',

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
            'send_customer_credentials' => 'nullable|boolean',

            // Nombre Archivo
            'GuardarEn' => 'nullable|string',

            // Document
            'type_document_id' => [
                'required',
                'in:15, 16, 19, 24',
                'exists:type_documents,id',
                new ResolutionSetting(),
            ],

            // Resolution number for document sending
            'resolution_number' => Rule::requiredIf(function(){
                if(auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->count() >= 2)
                  return true;
                else
                  return false;
            }),

            // Prefijo de la resolucion a utilizar

            'prefix' => Rule::requiredIf(function(){
                if(auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution_number', $this->resolution_number)->count() >= 2)
                    return true;
                else
                    return false;
            }),

            // Consecutive
            'number' => 'required|integer|between:'.optional($this->resolution)->from.','.optional($this->resolution)->to,

            // Date time
            'date' => 'nullable|date_format:Y-m-d|after_or_equal:'.optional($this->resolution)->date_from.'|before_or_equal:'.optional($this->resolution)->date_to,
            'time' => 'nullable|date_format:H:i:s',
            'due_date' => 'nullable|date_format:Y-m-d|after_or_equal:date',

            // Postal zone code
            'postal_zone_code' => 'nullable|required_if:type_document_id,=,15|numeric',

            // Notes
            'notes' => 'nullable|string',

            //Elaborado y Revisado
            'elaborated' => 'nullable|string',
            'reviewed' => 'nullable|string',

            // Tipo operacion
            'type_operation_id' => 'nullable|numeric|exists:type_operations,id',

            // Id moneda negociacion
            'idcurrency' => 'nullable|integer|exists:type_currencies,id',
            'calculationrate' => 'nullable|required_with:idcurrency|numeric',
            'calculationratedate' => 'nullable|required_with:idcurrency|date_format:Y-m-d',

            // Customer
            'customer' => 'required|array',
            'customer.identification_number' => 'required|alpha_num|between:1,15',
            'customer.dv' => 'nullable|numeric|digits:1|dian_dv:'.$this->customer["identification_number"],
            'customer.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'customer.type_organization_id' => 'nullable|exists:type_organizations,id',
            'customer.language_id' => 'nullable|exists:languages,id',
            'customer.country_id' => 'nullable|exists:countries,id',
            'customer.municipality_id' => 'nullable|exists:municipalities,id',
            'customer.municipality_id_fact' => 'nullable|exists:municipalities,codefacturador',
            'customer.type_regime_id' => 'nullable|exists:type_regimes,id',
            'customer.tax_id' => 'nullable|exists:taxes,id',
            'customer.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'customer.name' => 'required|string',
            'customer.phone' => 'nullable|string|max:20',
//            'customer.phone' => 'required_unless:customer.identification_number,222222222222|string|max:20',
            'customer.address' => 'nullable|string',
//            'customer.address' => 'required_unless:customer.identification_number,222222222222|string',
            'customer.email' => 'required_unless:customer.identification_number,222222222222|string|email',
//            'customer.merchant_registration' => 'required|string',
            'customer.merchant_registration' => 'nullable|string',

            // SMTP Server Parameters
            'smtp_parameters' => 'nullable|array',
            'smtp_parameters.host' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.port' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.username' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.password' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.encryption' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_address' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_name' => 'nullable|required_with:smtp_parameters|string',

            // Order Reference
            'order_reference' => 'nullable|array',
            'order_reference.id_order' => 'nullable|string',
            'order_reference.issue_date_order' => 'nullable|date_format:Y-m-d',

            // Delivery
            'delivery' => 'nullable|array',
            'delivery.language_id' => 'nullable|exists:languages,id',
            'delivery.country_id' => 'nullable|exists:countries,id',
            'delivery.municipality_id' => 'nullable|exists:municipalities,id',
            'delivery.address' => 'nullable|required_with:delivery|string',
            'delivery.actual_delivery_date' => 'nullable|required_with:delivery|date_format:Y-m-d',

            // Delivery Party
            'deliveryparty' => 'nullable|required_with:delivery|array',
            'deliveryparty.identification_number' => 'nullable|required_with:deliveryparty|numeric|digits_between:1,15',
//            'deliveryparty.dv' => 'nullable|required_with:delivery|numeric|digits:1|dian_dv:'.$this->deliveryparty["identification_number"],
            'deliveryparty.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'deliveryparty.type_organization_id' => 'nullable|exists:type_organizations,id',
            'deliveryparty.language_id' => 'nullable|exists:languages,id',
            'deliveryparty.country_id' => 'nullable|exists:countries,id',
            'deliveryparty.municipality_id' => 'nullable|exists:municipalities,id',
            'deliveryparty.type_regime_id' => 'nullable|exists:type_regimes,id',
            'deliveryparty.tax_id' => 'nullable|exists:taxes,id',
            'deliveryparty.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'deliveryparty.name' => 'nullable|required_with:deliveryparty|string',
            'deliveryparty.phone' => 'nullable|required_with:deliveryparty|string|max:20',
            'deliveryparty.address' => 'nullable|required_with:deliveryparty|string',
            'deliveryparty.email' => 'nullable|required_with:deliveryparty|string|email',
            'deliveryparty.merchant_registration' => 'nullable|string',

            // Payment form
            'payment_form' => 'nullable|array',
            'payment_form.payment_form_id' => 'nullable|exists:payment_forms,id',
            'payment_form.payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_form.payment_due_date' => 'nullable|required_if:payment_form.payment_form_id,=,2|after_or_equal:date|date_format:Y-m-d',
            'payment_form.duration_measure' => 'nullable|required_if:payment_form.payment_form_id,=,2|numeric|digits_between:1,3',

            // Allowance charges
            'allowance_charges' => 'nullable|array',
            'allowance_charges.*.charge_indicator' => 'nullable|required_with:allowance_charges|boolean',
            'allowance_charges.*.discount_id' => 'nullable|required_if:allowance_charges.*.charge_indicator,false|exists:discounts,id',
            'allowance_charges.*.allowance_charge_reason' => 'nullable|required_with:allowance_charges|string',
            'allowance_charges.*.amount' => 'nullable|required_with:allowance_charges|numeric',
            'allowance_charges.*.base_amount' => 'nullable|required_with:allowance_charges|numeric',

            // Tax totals
            'tax_totals' => 'nullable|array',
            'tax_totals.*.tax_id' => 'nullable|required_with:allowance_charges|exists:taxes,id',
            'tax_totals.*.tax_name' => 'nullable|required_if:tax_totals.*.tax_id,15|string',
            'tax_totals.*.percent' => 'nullable|required_unless:tax_totals.*.tax_id,10|numeric',
            'tax_totals.*.tax_amount' => 'nullable|required_with:allowance_charges|numeric',
            'tax_totals.*.taxable_amount' => 'nullable|required_with:allowance_charges|numeric',
            'tax_totals.*.unit_measure_id' => 'nullable|required_if:tax_totals.*.tax_id,10|exists:unit_measures,id',
            'tax_totals.*.per_unit_amount' => 'nullable|required_if:tax_totals.*.tax_id,10|numeric',
            'tax_totals.*.base_unit_measure' => 'nullable|required_if:tax_totals.*.tax_id,10|numeric',

            // Holding Tax totals
            'with_holding_tax_total' => 'nullable|array',
            'with_holding_tax_total.*.tax_id' => 'nullable|exists:taxes,id|numeric',
            'with_holding_tax_total.*.percent' => 'nullable|numeric',
            'with_holding_tax_total.*.tax_amount' => 'nullable|numeric',
            'with_holding_tax_total.*.taxable_amount' => 'nullable|numeric',
            'with_holding_tax_total.*.unit_measure_id' => 'nullable|exists:unit_measures,id',
            'with_holding_tax_total.*.per_unit_amount' => 'nullable|numeric',
            'with_holding_tax_total.*.base_unit_measure' => 'nullable|numeric',

            // Prepaid Payment
            'prepaid_payment' => 'nullable|array',
            'prepaid_payment.idpayment' => 'nullable|string',
            'prepaid_payment.paidamount' => 'nullable|numeric',
            'prepaid_payment.receiveddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payment.paiddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payment.instructionid' => 'nullable|string',
            // Prepaid Payments
            'prepaid_payments' => 'nullable|array',
            'prepaid_payments.*.idpayment' => 'nullable|string',
            'prepaid_payments.*.paidamount' => 'nullable|numeric',
            'prepaid_payments.*.receiveddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payments.*.paiddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payments.*.instructionid' => 'nullable|string',

            // Previous Balance
            'previous_balance' => 'nullable|numeric',

            // Legal monetary totals
            'legal_monetary_totals' => 'required|array',
            'legal_monetary_totals.line_extension_amount' => 'required|numeric',
            'legal_monetary_totals.tax_exclusive_amount' => 'required|numeric',
            'legal_monetary_totals.tax_inclusive_amount' => 'required|numeric',
            'legal_monetary_totals.allowance_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.charge_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.pre_paid_amount' => 'nullable|required_with:prepaid_payment|numeric',
            'legal_monetary_totals.payable_amount' => 'required|numeric',

            // Invoice lines
            'invoice_lines' => 'required|array',
            'invoice_lines.*.unit_measure_id' => 'required|exists:unit_measures,id',
            'invoice_lines.*.invoiced_quantity' => 'required|numeric',
            'invoice_lines.*.line_extension_amount' => 'required|numeric',
            'invoice_lines.*.free_of_charge_indicator' => 'required|boolean',
            'invoice_lines.*.reference_price_id' => 'nullable|required_if:invoice_lines.*.free_of_charge_indicator,true|exists:reference_prices,id',
            'invoice_lines.*.allowance_charges' => 'nullable|array',
            'invoice_lines.*.allowance_charges.*.charge_indicator' => 'nullable|required_with:invoice_lines.*.allowance_charges|boolean',
            'invoice_lines.*.allowance_charges.*.allowance_charge_reason' => 'nullable|required_with:invoice_lines.*.allowance_charges|string',
            'invoice_lines.*.allowance_charges.*.amount' => 'nullable|required_with:invoice_lines.*.allowance_charges|numeric',
            'invoice_lines.*.allowance_charges.*.base_amount' => 'nullable|required_if:invoice_lines.*.allowance_charges.*.charge_indicator,false|numeric',
            'invoice_lines.*.allowance_charges.*.multiplier_factor_numeric' => 'nullable|required_if:invoice_lines.*.allowance_charges.*.charge_indicator,true|numeric',
            'invoice_lines.*.tax_totals' => 'nullable|array',
            'invoice_lines.*.tax_totals.*.tax_id' => 'nullable|required_with:invoice_lines.*.tax_totals|exists:taxes,id',
            'invoice_lines.*.tax_totals.*.tax_name' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,15|string',
            'invoice_lines.*.tax_totals.*.tax_amount' => 'nullable|required_with:invoice_lines.*.tax_totals|numeric',
            'invoice_lines.*.tax_totals.*.taxable_amount' => 'nullable|required_with:invoice_lines.*.tax_totals|numeric',
            'invoice_lines.*.tax_totals.*.percent' => 'nullable|required_unless:invoice_lines.*.tax_totals.*.tax_id,10|numeric',
            'invoice_lines.*.tax_totals.*.unit_measure_id' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,10|exists:unit_measures,id',
            'invoice_lines.*.tax_totals.*.per_unit_amount' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,10|numeric',
            'invoice_lines.*.tax_totals.*.base_unit_measure' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,10|numeric',
            'invoice_lines.*.description' => 'required|string',
            'invoice_lines.*.seller_code' => 'nullable|required_if:type_document_id,=,24|string',
            'invoice_lines.*.notes' => 'nullable|string',
            'invoice_lines.*.code' => 'required|string',
            'invoice_lines.*.type_item_identification_id' => 'required|exists:type_item_identifications,id',
            'invoice_lines.*.price_amount' => 'required|numeric',
            'invoice_lines.*.base_quantity' => 'required|numeric',
        ];
    }
}
