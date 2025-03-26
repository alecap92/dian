<!DOCTYPE html>
<html lang="es">
{{-- <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head> --}}

<body>
    <table style="font-size: 10px; width: 100%; margin-top:-20px;">
        <tr>
            <!-- Primera Columna -->
            <td class="vertical-align-top" style="width: 25%;">
                <table>
                    <tr>
                        <td>CC o NIT:</td>
                        <td>{{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}}</td>
                    </tr>
                    <tr>
                        <td>Cliente:</td>
                        <td>{{$customer->name}}</td>
                    </tr>
                    <tr>
                        <td>Régimen:</td>
                        <td>{{$customer->company->type_regime->name}}</td>
                    </tr>
                    <tr>
                        <td>Medio de Pago:</td>
                        <td>
                            @foreach ($paymentForm as $paymentF)
                                {{$paymentF->nameMethod}}<br>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Segunda Columna -->
            <td class="vertical-align-top" style="width: 25%; padding-left: 1rem">
                <table>
                    <tr>
                        <td>Obligación:</td>
                        <td>{{$customer->company->type_liability->name}}</td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td>{{$customer->company->address}}</td>
                    </tr>
                    <tr>
                        <td>Ciudad:</td>
                        @if($customer->company->country->id == 46)
                            <td>{{$customer->company->municipality->name}} - {{$customer->company->country->name}}</td>
                        @else
                            <td>{{$customer->company->municipality_name}} - {{$customer->company->state_name}} - {{$customer->company->country->name}}</td>
                        @endif
                    </tr>
                    @if(isset($request['order_reference']['id_order']))
                    <tr>
                        <td>Número Pedido:</td>
                        <td>{{$request['order_reference']['id_order']}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Plazo Para Pagar:</td>
                        <td>{{$paymentForm[0]->duration_measure}} Días</td>
                    </tr>
                </table>
            </td>

            <!-- Tercera Columna -->
            <td class="vertical-align-top" style="width: 25%; padding-left: 1rem">
                <table>
                    <tr>
                        <td>Teléfono:</td>
                        <td>{{$customer->company->phone}}</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{$customer->email}}</td>
                    </tr>
                    <tr>
                        <td>Forma de Pago:</td>
                        <td>{{$paymentForm[0]->name}}</td>
                    </tr>
                    @if(isset($request['order_reference']['issue_date_order']))
                    <tr>
                        <td>Fecha Pedido:</td>
                        <td>{{$request['order_reference']['issue_date_order']}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Fecha Vencimiento:</td>
                        <td>{{$paymentForm[0]->payment_due_date}}</td>
                    </tr>
                </table>
            </td>
            <!-- Cuarta Columna -->
            <td class="vertical-align-top" style="width: 35%; text-align: center">
                <table>
                    <tr>
                        <td><img style="width: 120px;" src="{{$imageQr}}"></td>
                    </tr>
                    <!-- Puedes agregar más filas aquí si es necesario -->
                </table>
            </td>
        </tr>
    </table>

    <br>
    @isset($healthfields)
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100%;">INFORMACION REFERENCIAL SECTOR SALUD</th>
                </tr>
            </thead>
        </table>
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 12%;">Cod Prestador</th>
                    <th class="text-center" style="width: 25%;">Info. Contrat./Cobertura</th>
                    <th class="text-center" style="width: 18%;">Info. de Pagos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($healthfields->users_info as $item)
                    <tr>
                        <td>
                            <p style="font-size: 8px">{{$item->provider_code}}</p>
                        </td>
                        <td>
                            <p style="font-size: 8px">Modalidad Contratación: {{$item->health_contracting_payment_method()->name}}</p>
                            <p style="font-size: 8px">Nro. Contrato: {{$item->contract_number}}</p>
                            <p style="font-size: 8px">Cobertura: {{$item->health_coverage()->name}}</p>
                        </td>
                        <td>
                            <p style="font-size: 8px">Copago: {{number_format($item->co_payment, 2)}}</p>
                            <p style="font-size: 8px">Cuota Moderardora: {{number_format($item->moderating_fee, 2)}}</p>
                            <p style="font-size: 8px">Pagos Compartidos: {{number_format($item->shared_payment, 2)}}</p>
                            <p style="font-size: 8px">Anticipos: {{number_format($item->advance_payment, 2)}}</p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    @endisset
    <table class="table" style="margin-top:-12px;width: 100%;">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Código</th>
                <th class="text-center">Descripción</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">UM</th>
                <th class="text-center">Val. Unit</th>
                <th class="text-center">IVA/IC</th>
                <th class="text-center">Dcto</th>
                <th class="text-center">%</th>
                <th class="text-center">Val. Item</th>
            </tr>
        </thead>
        <tbody>
            <?php $ItemNro = 0; ?>
            @foreach($request['invoice_lines'] as $item)
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    @inject('um', 'App\UnitMeasure')
                    @if($item['description'] == 'Administración' or $item['description'] == 'Imprevisto' or $item['description'] == 'Utilidad')
                        <td>{{$ItemNro}}</td>
                        <td class="text-right">
                            {{$item['code']}}
                        </td>
                        <td>{{$item['description']}}</td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right">{{number_format($item['price_amount'], 2)}}</td>
                        <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'], 2)}}</td>
                        @if(isset($item['allowance_charges']))
                            <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'], 2)}}</td>
                            <td class="text-right">{{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}</td>
                        @else
                            <td class="text-right">{{number_format("0", 2)}}</td>
                            <td class="text-right">{{number_format("0", 2)}}</td>
                        @endif
                        <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['price_amount'], 2)}}</td>
                    @else
                        <td>{{$ItemNro}}</td>
                        <td>{{$item['code']}}</td>
                        <td>
                            @if(isset($item['notes']))
                                {{$item['description']}}
                                <p style="font-style: italic; font-size: 7px"><strong>{{$item['notes']}}</strong></p>
                            @else
                                {{$item['description']}}
                            @endif
                        </td>
                        <td class="text-right">{{number_format($item['invoiced_quantity'], 2)}}</td>
                        <td class="text-right">{{$um->findOrFail($item['unit_measure_id'])['name']}}</td>

                        @if(isset($item['tax_totals']))
                            @if(isset($item['allowance_charges']))
                                <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2)}}</td>
                            @endif
                        @else
                            @if(isset($item['allowance_charges']))
                                <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2)}}</td>
                            @endif
                        @endif

                        @if(isset($item['tax_totals']))
                            @if(isset($item['tax_totals'][0]['tax_amount']))
                                <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'] / $item['invoiced_quantity'], 2)}}</td>
                            @else
                                <td class="text-right">{{number_format(0, 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">E</td>
                        @endif

                        @if(isset($item['allowance_charges']))
                            <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'] / $item['invoiced_quantity'], 2)}}</td>
                            <td class="text-right">{{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}</td>
                            @if(isset($item['tax_totals']))
                                <td class="text-right">{{number_format(($item['line_extension_amount'] + ($item['tax_totals'][0]['tax_amount'])), 2)}}</td>
                            @else
                                <td class="text-right">{{number_format(($item['line_extension_amount']), 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">{{number_format("0", 2)}}</td>
                            <td class="text-right">{{number_format("0", 2)}}</td>
                            <td class="text-right">{{number_format($item['invoiced_quantity'] * ($item['line_extension_amount'] / $item['invoiced_quantity']), 2)}}</td>
                        @endif
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th class="text-center">Impuestos</th>
                <th class="text-center">Retenciones</th>
                <th class="text-center">Totales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 40%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Base</th>
                                <th class="text-center">Porcentaje</th>
                                <th class="text-center">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($request->tax_totals))
                                <?php $TotalImpuestos = 0; ?>
                                @foreach($request->tax_totals as $item)
                                    <tr>
                                        <?php $TotalImpuestos = $TotalImpuestos + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{$tax->findOrFail($item['tax_id'])['name']}}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        <td class="text-right">{{number_format($item['percent'], 2)}}%</td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <?php $TotalImpuestos = 0; ?>
                            @endif
                        </tbody>
                    </table>
                </td>
                <td style="width: 30%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Base</th>
                                <th class="text-center">Porcentaje</th>
                                <th class="text-center">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($withHoldingTaxTotal))
                                <?php $TotalRetenciones = 0; ?>
                                @foreach($withHoldingTaxTotal as $item)
                                    <tr>
                                        <?php $TotalRetenciones = $TotalRetenciones + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{$tax->findOrFail($item['tax_id'])['name']}}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        <td class="text-right">{{number_format($item['percent'], 2)}}%</td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </td>
                <td style="width: 30%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">Concepto</th>
                                <th class="text-center">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nro Lineas:</td>
                                <td class="text-right">{{$ItemNro}}</td>
                            </tr>
                            <tr>
                                <td>Base:</td>
                                <td class="text-right">{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                            </tr>
                            <tr>
                                <td>Impuestos:</td>
                                <td class="text-right">{{number_format($TotalImpuestos, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Retenciones:</td>
                                <td class="text-right">{{number_format($TotalRetenciones, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Descuentos:</td>
                                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                    <td class="text-right">{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                @endif
                            </tr>
                            @if(isset($request->previous_balance))
                                @if($request->previous_balance > 0)
                                    <tr>
                                        <td>Saldo Anterior:</td>
                                        <td class="text-right">{{number_format($request->previous_balance, 2)}}</td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <td>Total Factura - Descuentos:</td>
                                @if(isset($request->tarifaica))
                                    @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                        @if(isset($request->previous_balance))
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                                        @else
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                                        @endif
                                    @else
                                        @if(isset($request->previous_balance))
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                                        @else
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2)}}</td>
                                        @endif
                                    @endif
                                @else
                                    @if(isset($request->previous_balance))
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                                    @else
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                                    @endif
                                @endif
                            </tr>

                            <tr>
                                <td>Total a Pagar</td>
                                @if(isset($request->tarifaica))
                                    @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                        @if(isset($request->previous_balance))
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                                        @else
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                                        @endif
                                    @else
                                        @if(isset($request->previous_balance))
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                                        @else
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2)}}</td>
                                        @endif
                                    @endif
                                @else
                                    @if(isset($request->previous_balance))
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                                    @else
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                                    @endif
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    @inject('Varios', 'App\Custom\NumberSpellOut')
    <div class="text-right" style="margin-top: -25px;">
        <div>
            <p style="font-size: 12pt">
                @php
                    // Inicializamos con payable_amount
                    $totalAmount = $request->legal_monetary_totals['payable_amount'];

                    // Verificamos si existe previous_balance
                    if (isset($request->previous_balance)) {
                        $totalAmount += $request->previous_balance;
                    }

                    // Verificamos si existen retenciones y las restamos
                    if (isset($TotalRetenciones)) {
                        $totalAmount -= $TotalRetenciones;
                    }

                    // Finalmente, redondeamos el total a dos decimales
                    $totalAmount = round($totalAmount, 2);

                    // Definimos la moneda
                    $idcurrency = $request->idcurrency ?? null;
                @endphp
                <p><strong>SON</strong>: {{$Varios->convertir($totalAmount, $idcurrency)}} M/CTE*********.</p>
            </p>
        </div>
    </div>

    @if(isset($notes))
    <div style="margin-top:-10px; border: 1px solid grey; border-radius: 1px; padding: 1px;">
        <div id="note">
            <p><strong>NOTAS:</strong>{{$notes}}</p>
        </div>
    </div>
    @endif

    @if(isset($request->head_note))
    <p style="margin-top:1px;text-align: center;"><strong>{{$request->head_note}}</strong></p>
    @endif

{{--
<div class="summary" >
    <div class="text-word" id="note">
        @if(isset($request->disable_confirmation_text))
            @if(!$request->disable_confirmation_text)
                <p style="font-style: italic;">INFORME EL PAGO AL TELEFONO {{$company->phone}} o al e-mail {{$user->email}}<br>
                    {{-- <br>
                    <div id="firma">
                        <p><strong>FIRMA ACEPTACIÓN:</strong></p><br>
                        <p><strong>CC:</strong></p><br>
                        <p><strong>FECHA:</strong></p><br>
                    </div>
                </p>
            @endif
        @endif
    </div>
    @if(isset($firma_facturacion) and !is_null($firma_facturacion))
        <table style="font-size: 10px">
            <tr>
                <td class="vertical-align-top" style="width: 50%; text-align: right">
                    <img style="width: 250px;" src="{{$firma_facturacion}}">
                </td>
            </tr>
        </table>
    @endif
</div>

--}}

<div>
    <hr style="margin-top: -10px;">
    <p style="font-size: 10px;margin-top: -9px;" id='mi-texto'>Factura No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generación: {{$date}} - {{$time}}<br> CUFE: <strong>{{$cufecude}}</strong></p>
    @isset($request->foot_note)
        <p style="font-size: 9px;" id='mi-texto-1'><strong>{{$request->foot_note}}</strong></p>
    @endisset
</div>

</body>
</html>
