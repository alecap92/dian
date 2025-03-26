
<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; ?>
<NominaIndividualDeAjuste
    xmlns="dian:gov:co:facturaelectronica:NominaIndividualDeAjuste"
    xmlns:xs="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"
    xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    SchemaLocation=""
    xsi:schemaLocation="dian:gov:co:facturaelectronica:NominaIndividualDeAjuste NominaIndividualDeAjusteElectronicaXSD.xsd">
    {{-- UBLExtensions --}}
    @include('xml._ubl_extensions_payroll')
    <TipoNota>{{$type_note}}</TipoNota>
    @if($type_note == 1)
        <Reemplazar>
            {{-- Replace Predecessor --}}
            @include('xml._replace_predecessor_payroll')
            {{-- Period --}}
            @include('xml._period_payroll')
            {{-- Secuence Number --}}
            @include('xml._secuence_number_payroll')
            {{-- XML Generation Place --}}
            @include('xml._generation_place_payroll')
            {{-- XML Provider --}}
            @include('xml._provider_payroll')
            {{-- General Information --}}
            @include('xml._general_information_payrollNote')
            {{-- Employer --}}
            @include('xml._employer')
            {{-- Worker --}}
            @include('xml._worker')
            {{-- Payment --}}
            @include('xml._payment_payroll')
            {{-- Accrued --}}
            @include('xml._accrued_payroll')
            {{-- Deductions --}}
            @include('xml._deductions_payroll')
            <DevengadosTotal>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->accrued_total)}}</DevengadosTotal>
            <DeduccionesTotal>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->deductions_total)}}</DeduccionesTotal>
            <ComprobanteTotal>{{preg_replace("/[\r\n|\n|\r|',']+/", "", number_format($accrued->accrued_total - $deductions->deductions_total, 2))}}</ComprobanteTotal>
        </Reemplazar>
    @else
        <Eliminar>
            {{-- Delete Predecessor --}}
            @include('xml._delete_predecessor_payroll')
            {{-- Secuence Number --}}
            @include('xml._secuence_number_payroll')
            {{-- XML Generation Place --}}
            @include('xml._generation_place_payroll')
            {{-- XML Provider --}}
            @include('xml._provider_payroll')
            {{-- General Information --}}
            @include('xml._general_information_payrollNote')
            {{-- Employer --}}
            @include('xml._employer')
        </Eliminar>
    @endif
</NominaIndividualDeAjuste>
