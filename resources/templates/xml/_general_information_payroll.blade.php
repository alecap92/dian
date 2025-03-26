<InformacionGeneral
    Version="V1.0: Documento Soporte de Pago de Nómina Electrónica"
    Ambiente="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->payroll_type_environment->code)}}"
    TipoXML="102"
    TRM="1"
    CUNE="===========CUNE==========="
    EncripCUNE="{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->cufe_algorithm)}}"
    FechaGen="{{preg_replace("/[\r\n|\n|\r]+/", "", $period->issue_date ?? Carbon\Carbon::now()->format('Y-m-d'))}}"
    HoraGen="{{preg_replace("/[\r\n|\n|\r]+/", "", $time ?? Carbon\Carbon::now()->format('H:i:s'))}}-05:00"
    PeriodoNomina="{{preg_replace("/[\r\n|\n|\r]+/", "", $request->payroll_period_id)}}"
    TipoMoneda="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}"></InformacionGeneral>
    @isset($request->notes)
        <Notas>{{preg_replace("/[\r\n|\n|\r]+/", "", $request->notes)}}</Notas>
    @endisset
