<Deducciones>
    <Salud
        Porcentaje="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->eps_type_law_deductions->percentage)}}"
        Deduccion="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->eps_deduction)}}"
    ></Salud>
    <FondoPension
        Porcentaje="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->pension_type_law_deductions->percentage)}}"
        Deduccion="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->pension_deduction)}}"
    ></FondoPension>
    @if(isset($deductions->fondossp_type_law_deductions_id) || isset($deductions->fondossp_sub_type_law_deductions_id))
        <FondoSP
            @if(isset($deductions->fondossp_type_law_deductions_id))
                Porcentaje="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->fondossp_type_law_deductions->percentage)}}"
                DeduccionSP="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->fondosp_deduction_SP)}}"
            @endif
            @if(isset($deductions->fondossp_sub_type_law_deductions_id))
                PorcentajeSub="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->fondossp_sub_type_law_deductions->percentage)}}"
                DeduccionSub="{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->fondosp_deduction_sub)}}"
            @endif
        ></FondoSP>
    @endif
    {{-- Labor Union  --}}
    @if(isset($deductions->labor_union))
        <Sindicatos>
            @include('xml._general_items_payroll', ['node' => 'Sindicato', 'items' => $deductions->labor_union])
        </Sindicatos>
    @endif
    {{-- Sanctions --}}
    @if(isset($deductions->sanctions))
        <Sanciones>
            @include('xml._general_items_payroll', ['node' => 'Sancion', 'items' => $deductions->sanctions])
        </Sanciones>
    @endif
    {{-- Orders --}}
    @if(isset($deductions->orders))
        <Libranzas>
            @include('xml._general_items_payroll', ['node' => 'Libranza', 'items' => $deductions->orders])
        </Libranzas>
    @endif
    {{-- Third Party Payments --}}
    @if(isset($deductions->third_party_payments))
        <PagosTerceros>
            @include('xml._general_items_payroll', ['node' => 'PagoTercero', 'items' => $deductions->third_party_payments])
        </PagosTerceros>
    @endif
    {{-- Advances --}}
    @if(isset($deductions->advances))
        <Anticipos>
            @include('xml._general_items_payroll', ['node' => 'Anticipo', 'items' => $deductions->advances])
        </Anticipos>
    @endif
    {{-- Other Deductionss --}}
    @if(isset($deductions->other_deductions))
        <OtrasDeducciones>
            @include('xml._general_items_payroll', ['node' => 'OtraDeduccion', 'items' => $deductions->other_deductions])
        </OtrasDeducciones>
    @endif
    @if(isset($deductions->voluntary_pension))
        <PensionVoluntaria>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->voluntary_pension)}}</PensionVoluntaria>
    @endif
    @if(isset($deductions->withholding_at_source))
        <RetencionFuente>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->withholding_at_source)}}</RetencionFuente>
    @endif
    @if(isset($deductions->afc))
        <AFC>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->afc)}}</AFC>
    @endif
    @if(isset($deductions->cooperative))
        <Cooperativa>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->cooperative)}}</Cooperativa>
    @endif
    @if(isset($deductions->tax_liens))
        <EmbargoFiscal>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->tax_liens)}}</EmbargoFiscal>
    @endif
    @if(isset($deductions->supplementary_plan))
        <PlanComplementarios>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->supplementary_plan)}}</PlanComplementarios>
    @endif
    @if(isset($deductions->education))
        <Educacion>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->education)}}</Educacion>
    @endif
    @if(isset($deductions->refund))
        <Reintegro>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->refund)}}</Reintegro>
    @endif
    @if(isset($deductions->debt))
        <Deuda>{{preg_replace("/[\r\n|\n|\r]+/", "", $deductions->debt)}}</Deuda>
    @endif
</Deducciones>
