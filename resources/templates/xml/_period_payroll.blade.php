<Periodo FechaIngreso="{{preg_replace("/[\r\n|\n|\r]+/", "", $period->admision_date)}}"
    @isset($period->retirement_date)
        FechaRetiro="{{preg_replace("/[\r\n|\n|\r]+/", "", $period->retirement_date)}}"
    @endisset
        FechaLiquidacionInicio="{{preg_replace("/[\r\n|\n|\r]+/", "", $period->settlement_start_date)}}"
        FechaLiquidacionFin="{{preg_replace("/[\r\n|\n|\r]+/", "", $period->settlement_end_date)}}"
        TiempoLaborado="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($period->worked_time, 2, '.', ''))}}"
        FechaGen="{{preg_replace("/[\r\n|\n|\r]+/", "", $period->issue_date)}}"></Periodo>
