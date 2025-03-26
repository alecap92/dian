@foreach ($extraHours as $key => $extraHour)
    <{{$node}}
        HoraInicio="{{preg_replace("/[\r\n|\n|\r]+/", "", $extraHour->start_time)}}"
        HoraFin="{{preg_replace("/[\r\n|\n|\r]+/", "", $extraHour->end_time)}}"
        Cantidad="{{preg_replace("/[\r\n|\n|\r]+/", "", $extraHour->quantity)}}"
        Porcentaje="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($extraHour->type_overtime_surcharge->percentage, 2))}}"
        Pago="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($extraHour->payment, 2, '.', ''))}}"></{{$node}}>
@endforeach
