<Devengados>
    <Basico
        DiasTrabajados="{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->worked_days)}}"
        SueldoTrabajado="{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->salary)}}"></Basico>
    @if(isset($accrued->transportation_allowance) || isset($accrued->salary_viatics) ||isset($accrued->non_salary_viatics))
        <Transporte
            @if(isset($accrued->transportation_allowance))
                AuxilioTransporte="{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->transportation_allowance)}}"
            @endif
            @if(isset($accrued->salary_viatics))
                ViaticoManuAlojS="{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->salary_viatics)}}"
            @endif
            @if(isset($accrued->non_salary_viatics))
                ViaticoManuAlojNS="{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->non_salary_viatics)}}"
            @endif
        ></Transporte>
    @else
        <Transporte></Transporte>
    @endif
    {{-- Day Overtime  --}}
    @if(isset($accrued->HEDs))
        <HEDs>
            @include('xml._extra_hours_payroll', ['node' => 'HED', 'extraHours' => $accrued->HEDs])
        </HEDs>
    @endif
    {{-- Night Overtime  --}}
    @if(isset($accrued->HENs))
        <HENs>
            @include('xml._extra_hours_payroll', ['node' => 'HEN', 'extraHours' => $accrued->HENs])
        </HENs>
    @endif
    {{-- Night surcharge  --}}
    @if(isset($accrued->HRNs))
        <HRNs>
            @include('xml._extra_hours_payroll', ['node' => 'HRN', 'extraHours' => $accrued->HRNs])
        </HRNs>
    @endif
    {{-- Day Time Overtime on Sunday and Holidays  --}}
    @if(isset($accrued->HEDDFs))
        <HEDDFs>
            @include('xml._extra_hours_payroll', ['node' => 'HEDDF', 'extraHours' => $accrued->HEDDFs])
        </HEDDFs>
    @endif
    {{-- Day Time Top-Up Hours on Sundays and Holidays --}}
    @if(isset($accrued->HRDDFs))
        <HRDDFs>
            @include('xml._extra_hours_payroll', ['node' => 'HRDDF', 'extraHours' => $accrued->HRDDFs])
        </HRDDFs>
    @endif
    {{-- Night Time Overtime on Sunday and Holidays  --}}
    @if(isset($accrued->HENDFs))
        <HENDFs>
            @include('xml._extra_hours_payroll', ['node' => 'HENDF', 'extraHours' => $accrued->HENDFs])
        </HENDFs>
    @endif
    {{-- Night Time Top-Up Hours on Sundays and Holidays --}}
    @if(isset($accrued->HRNDFs))
        <HRNDFs>
            @include('xml._extra_hours_payroll', ['node' => 'HRNDF', 'extraHours' => $accrued->HRNDFs])
        </HRNDFs>
    @endif
    {{-- Common And Paid Vacations --}}
    @if(isset($accrued->common_vacation) || isset($accrued->paid_vacation))
        <Vacaciones>
            @if(isset($accrued->common_vacation))
                @include('xml._general_items_payroll', ['node' => 'VacacionesComunes', 'items' => $accrued->common_vacation])
            @endif
            @if(isset($accrued->paid_vacation))
                @include('xml._general_items_payroll', ['node' => 'VacacionesCompensadas', 'items' => $accrued->paid_vacation])
            @endif
        </Vacaciones>
    @endif
    {{-- Service Bonus --}}
    @if(isset($accrued->service_bonus))
        @include('xml._general_items_payroll', ['node' => 'Primas', 'items' => $accrued->service_bonus])
    @endif
    {{-- Severance --}}
    @if(isset($accrued->severance))
        @include('xml._general_items_payroll', ['node' => 'Cesantias', 'items' => $accrued->severance])
    @endif
    {{-- Leave For Work Disabilities --}}
    @if(isset($accrued->work_disabilities))
        <Incapacidades>
            @include('xml._general_items_payroll', ['node' => 'Incapacidad', 'items' => $accrued->work_disabilities])
        </Incapacidades>
    @endif
    {{-- Work Licenses --}}
    @if(isset($accrued->maternity_leave) || isset($accrued->paid_leave) || isset($accrued->non_paid_leave))
        <Licencias>
            @if(isset($accrued->maternity_leave))
                @include('xml._general_items_payroll', ['node' => 'LicenciaMP', 'items' => $accrued->maternity_leave])
            @endif
            @if(isset($accrued->paid_leave))
                @include('xml._general_items_payroll', ['node' => 'LicenciaR', 'items' => $accrued->paid_leave])
            @endif
            @if(isset($accrued->non_paid_leave))
                @include('xml._general_items_payroll', ['node' => 'LicenciaNR', 'items' => $accrued->non_paid_leave])
            @endif
        </Licencias>
    @endif
    {{-- Bonuses --}}
    @if(isset($accrued->bonuses))
        <Bonificaciones>
            @include('xml._general_items_payroll', ['node' => 'Bonificacion', 'items' => $accrued->bonuses])
        </Bonificaciones>
    @endif
    {{-- Aid --}}
    @if(isset($accrued->aid))
        <Auxilios>
            @include('xml._general_items_payroll', ['node' => 'Auxilio', 'items' => $accrued->aid])
        </Auxilios>
    @endif
    {{-- Legal Strike --}}
    @if(isset($accrued->legal_strike))
        <HuelgasLegales>
            @include('xml._general_items_payroll', ['node' => 'HuelgaLegal', 'items' => $accrued->legal_strike])
        </HuelgasLegales>
    @endif
    {{-- Oter Concepts --}}
    @if(isset($accrued->other_concepts))
        <OtrosConceptos>
            @include('xml._general_items_payroll', ['node' => 'OtroConcepto', 'items' => $accrued->other_concepts])
        </OtrosConceptos>
    @endif
    {{-- Compensations --}}
    @if(isset($accrued->compensations))
        <Compensaciones>
            @include('xml._general_items_payroll', ['node' => 'Compensacion', 'items' => $accrued->compensations])
        </Compensaciones>
    @endif
    {{-- EPCTV Bonuses --}}
    @if(isset($accrued->epctv_bonuses))
        <BonoEPCTVs>
            @include('xml._general_items_payroll', ['node' => 'BonoEPCTV', 'items' => $accrued->epctv_bonuses])
        </BonoEPCTVs>
    @endif
    {{-- Commissions --}}
    @if(isset($accrued->commissions))
        <Comisiones>
            @include('xml._general_items_payroll', ['node' => 'Comision', 'items' => $accrued->commissions])
        </Comisiones>
    @endif
    {{-- Third Party Payments --}}
    @if(isset($accrued->third_party_payments))
        <PagosTerceros>
            @include('xml._general_items_payroll', ['node' => 'PagoTercero', 'items' => $accrued->third_party_payments])
        </PagosTerceros>
    @endif
    {{-- Advances --}}
    @if(isset($accrued->advances))
        <Anticipos>
            @include('xml._general_items_payroll', ['node' => 'Anticipo', 'items' => $accrued->advances])
        </Anticipos>
    @endif
    {{-- Other Items --}}
    @if(isset($accrued->endowment))
        <Dotacion>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->endowment)}}</Dotacion>
    @endif
    @if(isset($accrued->sustenance_support))
        <ApoyoSost>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->sustenance_support)}}</ApoyoSost>
    @endif
    @if(isset($accrued->telecommuting))
        <Teletrabajo>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->telecommuting)}}</Teletrabajo>
    @endif
    @if(isset($accrued->withdrawal_bonus))
        <BonifRetiro>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->withdrawal_bonus)}}</BonifRetiro>
    @endif
    @if(isset($accrued->compensation))
        <Indemnizacion>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->compensation)}}</Indemnizacion>
    @endif
    @if(isset($accrued->refund))
        <Reintegro>{{preg_replace("/[\r\n|\n|\r]+/", "", $accrued->refund)}}</Reintegro>
    @endif
</Devengados>
