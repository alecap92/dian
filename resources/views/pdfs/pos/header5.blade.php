
<table width="100%">
    <tr>
        <td style="width: 100%;" class="text-center vertical-align-top">
            <div id="reference">
                <p>DOCUMENTO EQUIVALENTE ELECTRONICO DEL TIQUETE DE MAQUINA REGISTRADORA CON SISTEMA P.O.S. No</p>
                <p style="color: red;
                    font-weight: bold;
                    font-size: 8px;
                    margin-bottom: 4px;">{{$resolution->prefix}} - {{$request->number}}</p>
                <p style="color: red;
                    font-weight: bold;
                    font-size: 8px;
                    margin-bottom: 4px;">Fecha Emisión: {{$date}}</p>
                <p>Fecha Validación DIAN: {{$date}}<br>
                    Hora Validación DIAN: {{$time}}</p>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 100%; padding: 0 1rem;" class="text-center vertical-align-top">
            <div id="empresa-header">
                <strong>{{$user->name}}</strong><br>
                @if(isset($request->establishment_name) && $request->establishment_name != 'Oficina Principal')
                    <strong>{{$request->establishment_name}}</strong><br>
                @endif
            </div>
            <div id="empresa-header1">
                @if(isset($request->ivaresponsable))
                    @if($request->ivaresponsable != $company->type_regime->name)
                        <p style="font-size: 6px">NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - {{$request->ivaresponsable}} - Obligación: {{$company->type_liability->name}}</p>
                    @else
                        <p style="font-size: 6px">NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - Obligación: {{$company->type_liability->name}}</p>
                    @endif
                @else
                    <p style="font-size: 6px">NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - Obligación: {{$company->type_liability->name}}</p>
                @endif
                @if(isset($request->nombretipodocid))
                    <p style="font-size: 6px">Tipo Documento ID: {{$request->nombretipodocid}}</p><br>
                @endif
                @if(isset($request->tarifaica) && $request->tarifaica != '100')
                    <p style="font-size: 6px">TARIFA ICA: {{$request->tarifaica}}%</p>
                @endif
                @if(isset($request->tarifaica) && isset($request->actividadeconomica))
                    -
                @endif
                @if(isset($request->actividadeconomica))
                    <p style="font-size: 6px">ACTIVIDAD ECONOMICA: {{$request->actividadeconomica}}</p><br>
                @else
                @endif
                @if(isset($request->seze))
                    <?php
                        $aseze = substr($request->seze, 0, strpos($request->seze, '-', 0));
                        $asociedad = substr($request->seze, strpos($request->seze, '-', 0) + 1);
                    ?>
                    <p style="font-size: 6px">Regimen SEZE Año: {{$aseze}} Constitución Sociedad Año: {{$asociedad}}</p><br>
                @endif
                <p style="font-size: 6px">Resolución de Facturación Electrónica No. {{$resolution->resolution}} de {{$resolution->resolution_date}}, Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} Al {{$resolution->to}} - Vigencia Desde: {{$resolution->date_from}} Hasta: {{$resolution->date_to}}</p>
                <p style="font-size: 6px">REPRESENTACION GRAFICA DE DOCUMENTO EQUIVALENTE ELECTRONICO DEL TIQUETE DE MAQUINA REGISTRADORA CON SISTEMA P.O.S.</p>
                @if(isset($request->establishment_address))
                    <p style="font-size: 6px">{{$request->establishment_address}} -</p>
                @else
                    <p style="font-size: 6px">{{$company->address}} -</p>
                @endif
                @inject('municipality', 'App\Municipality')
                @if(isset($request->establishment_municipality))
                    <p style="font-size: 6px">{{$municipality->findOrFail($request->establishment_municipality)['name']}} - {{$municipality->findOrFail($request->establishment_municipality)['department']['name']}} - {{$company->country->name}}</p>
                @else
                    <p style="font-size: 6px">{{$company->municipality->name}} - {{$municipality->findOrFail($company->municipality->id)['department']['name']}} - {{$company->country->name}}</p>
                @endif
                @if(isset($request->establishment_phone))
                    <p style="font-size: 6px">Teléfono - {{$request->establishment_phone}}</p>
                @else
                    <p style="font-size: 6px">Teléfono - {{$company->phone}}</p>
                @endif
                @if(isset($request->establishment_email))
                    <p style="font-size: 6px">E-mail: {{$request->establishment_email}} </p>
                @else
                    <p style="font-size: 6px">E-mail: {{$user->email}} </p>
                @endif
                @if (isset($request->seze))
                    <p style="font-size: 6px">FAVOR ABSTENERSE DE PRACTICAR RETENCION EN LA FUENTE REGIMEN ESPECIAL DECRETO 2112 DE 2019</p>
               @endif
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 80%; text-align: center;" class="vertical-align-top">
            <img  style="width: 136px; height: auto;" src="{{$imgLogo}}" alt="logo">
        </td>
    </tr>
</table>
