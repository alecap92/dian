@foreach ($items as $key => $item)
    <{{$node}}
        @if(@isset($item->start_date))
            FechaInicio="{{preg_replace("/[\r\n|\n|\r]+/", "", $item->start_date)}}"
        @endisset
        @if(@isset($item->end_date))
            FechaFin="{{preg_replace("/[\r\n|\n|\r]+/", "", $item->end_date)}}"
        @endisset
        @if(@isset($item->quantity))
            Cantidad="{{preg_replace("/[\r\n|\n|\r]+/", "", $item->quantity)}}"
        @endif
        @if(@isset($item->payment))
            Pago="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->payment, 2, '.', ''))}}"
        @endif
        @if(@isset($item->type))
            Tipo="{{preg_replace("/[\r\n|\n|\r]+/", "", $item->type)}}"
        @endif
        @if(@isset($item->paymentS))
            PagoS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->paymentS, 2, '.', ''))}}"
        @endif
        @if(@isset($item->paymentNS))
            PagoNS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->paymentNS, 2, '.', ''))}}"
        @endif
        @if(@isset($item->percentage))
            Porcentaje="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->percentage, 2, '.', ''))}}"
        @endif
        @if(@isset($item->interest_payment))
            PagoIntereses="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->interest_payment, 2, '.', ''))}}"
        @endif
        @if(@isset($item->salary_bonus))
            BonificacionS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->salary_bonus, 2, '.', ''))}}"
        @endif
        @if(@isset($item->non_salary_bonus))
            BonificacionNS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->non_salary_bonus, 2, '.', ''))}}"
        @endif
        @if(@isset($item->salary_assistance))
            AuxilioS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->salary_assistance, 2, '.', ''))}}"
        @endif
        @if(@isset($item->non_salary_assistance))
            AuxilioNS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->non_salary_assistance, 2, '.', ''))}}"
        @endif
        @if(@isset($item->description_concept))
            DescripcionConcepto="{{preg_replace("/[\r\n|\n|\r]+/", "", $item->description_concept)}}"
        @endif
        @if(@isset($item->description))
            Descripcion="{{preg_replace("/[\r\n|\n|\r]+/", "", $item->description)}}"
        @endif
        @if(@isset($item->salary_concept))
            ConceptoS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->salary_concept, 2, '.', ''))}}"
        @endif
        @if(@isset($item->non_salary_concept))
            ConceptoNS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->non_salary_concept, 2, '.', ''))}}"
        @endif
        @if(@isset($item->ordinary_compensation))
            CompensacionO="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->ordinary_compensation, 2, '.', ''))}}"
        @endif
        @if(@isset($item->extraordinary_compensation))
            CompensacionE="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->extraordinary_compensation, 2, '.', ''))}}"
        @endif
        @if(@isset($item->salary_food_payment))
            PagoAlimentacionS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->salary_food_payment, 2, '.', ''))}}"
        @endif
        @if(@isset($item->non_salary_food_payment))
            PagoAlimentacionNS="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->non_salary_food_payment, 2, '.', ''))}}"
        @endif
        @if(@isset($item->deduction))
            Deduccion="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->deduction, 2, '.', ''))}}"
        @endif
        @if(@isset($item->public_sanction))
            SancionPublic="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->public_sanction, 2, '.', ''))}}"
        @endif
        @if(@isset($item->private_sanction))
            SancionPriv="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->private_sanction, 2, '.', ''))}}"
        @endif
        @if(@isset($item->voluntary_pension))
            PensionVoluntaria="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->voluntary_pension, 2, '.', ''))}}"
        @endif
        @if(@isset($item->withholding_at_source))
            RetencionFuente="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->withholding_at_source, 2, '.', ''))}}"
        @endif
        @if(@isset($item->ica))
            ICA="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->ica, 2, '.', ''))}}"
        @endif
        @if(@isset($item->afc))
            AFC="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->afc, 2, '.', ''))}}"
        @endif
        @if(@isset($item->cooperative))
            Cooperativa=">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->cooperative, 2, '.', ''))}}"
        @endif
        @if(@isset($item->tax_liens))
            EmbargoFiscal="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->tax_liens, 2, '.', ''))}}"
        @endif
        @if(@isset($item->supplementary_plan))
            PlanComplementarios="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->supplementary_plan, 2, '.', ''))}}"
        @endif
        @if(@isset($item->education))
            Educacion="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->education, 2, '.', ''))}}"
        @endif
        @if(@isset($item->refund))
            Reintegro="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->refund, 2, '.', ''))}}"
        @endif
        @if(@isset($item->debt))
            Deuda="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->debt, 2, '.', ''))}}"
        @endif
        @if(@isset($item->commission))
            >{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->commission, 2, '.', ''))}}</{{$node}}>
        @endif
        @if(@isset($item->third_party_payment))
            >{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->third_party_payment, 2, '.', ''))}}</{{$node}}>
        @endif
        @if(@isset($item->advance))
            >{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->advance, 2, '.', ''))}}</{{$node}}>
        @endif
        @if(@isset($item->other_deduction))
            >{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($item->other_deduction, 2, '.', ''))}}</{{$node}}>
        @endif
    @if(!@isset($item->commission))
        @if(!@isset($item->third_party_payment))
            @if(!@isset($item->advance))
                @if(!@isset($item->other_deduction))
                    ></{{$node}}>
                @endif
            @endif
        @endif
    @endif
@endforeach
