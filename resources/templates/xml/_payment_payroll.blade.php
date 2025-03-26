<Pago
    Forma="1"
    Metodo="{{preg_replace("/[\r\n|\n|\r]+/", "", $payment->payment_method->code)}}"
    @isset($payment->bank_name)
        Banco="{{preg_replace("/[\r\n|\n|\r]+/", "", $payment->bank_name)}}"
        TipoCuenta="{{preg_replace("/[\r\n|\n|\r]+/", "", $payment->account_type)}}"
        NumeroCuenta="{{preg_replace("/[\r\n|\n|\r]+/", "", $payment->account_number)}}"
    @endisset
></Pago>
<FechasPagos>
    @foreach ($payment_dates as $key => $payment_date)
        <FechaPago>{{preg_replace("/[\r\n|\n|\r]+/", "", $payment_date->payment_date)}}</FechaPago>
    @endforeach
</FechasPagos>
