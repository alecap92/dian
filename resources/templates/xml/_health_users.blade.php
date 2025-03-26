@foreach ($healthUsers as $key => $healthUser)
    <Collection schemeName="Usuario">
        <AdditionalInformation>
            <Name>CODIGO_PRESTADOR</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->provider_code)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>MODALIDAD_PAGO</Name>
            <Value schemeName="salud_modalidad_pago.gc" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->health_contracting_payment_method()->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->health_contracting_payment_method()->name)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>COBERTURA_PLAN_BENEFICIOS</Name>
            <Value schemeName="salud_cobertura.gc" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->health_coverage()->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->health_coverage()->name)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>NUMERO_CONTRATO</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->contract_number)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>NUMERO_POLIZA</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->policy_number)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>COPAGO</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->co_payment)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>CUOTA_MODERADORA</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->moderating_fee)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>PAGOS_COMPARTIDOS</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->shared_payment)}}</Value>
        </AdditionalInformation>
        <AdditionalInformation>
            <Name>ANTICIPO</Name>
            <Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $healthUser->advance_payment)}}</Value>
        </AdditionalInformation>
    </Collection>
@endforeach
