<!DOCTYPE html>
<html lang="es">
{{-- <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head> --}}

<body margin-top:20px margin-bottom:100px>
    @if(isset($request->head_note))
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-condensed table-striped table-responsive">
                    <thead>
                        <tr>
                            <th class="text-center"><p><strong>{{$request->head_note}}<br/>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    @endif
    <table style="font-size: 12px">
        <tr>
            @if($request->type_document_id == 9 || ($request->type_document_id == 10 && $request->type_note == 1))
                <td class="vertical-align-top" style="width: 50%;">
                    <table>
                        <tr>
                            <td>Documento ID:</td>
                            <td>{{$worker->identification_number}}</td>
                        </tr>
                        <tr>
                            <td>Nombre:</td>
                            <td>{{$worker->first_name}} {{$worker->middle_name ?? ' '}} {{$worker->surname ?? ' '}} {{$worker->second_surname ?? NULL}}</td>
                        </tr>
                        @if(isset($request->worker_code) || isset($worker->worker_code))
                            <tr>
                                <td>Codigo Interno:</td>
                                @if(isset($request->worker_code))
                                    <td>{{$request->worker_code}}</td>
                                @else
                                   <td>{{$worker->worker_code}}</td>
                                @endif
                            </tr>
                        @endif
                        <tr>
                            <td>Basico Mensual:</td>
                            <td>{{number_format($worker->salary, 2)}}</td>
                        </tr>
                        <tr>
                            <td>Dirección:</td>
                            <td>{{$worker->address}}</td>
                        </tr>
                        <tr>
                            <td>Ciudad:</td>
                            <td> {{$worker->municipality->name}} - {{$worker->country->name}} </td>
                        </tr>
                        <tr>
                            <td>Tipo de Contrato:</td>
                            <td>{{$worker->type_contract->name}}</td>
                        </tr>
                        <tr>
                            <td>Tipo de Trabajador:</td>
                            <td>{{$worker->type_worker->name}}</td>
                        </tr>
                    </table>
                </td>
                <td class="vertical-align-top" style="width: 35%; padding-left: 1rem">
                    <table>
                        <tr>
                            <td>Forma de Pago:</td>
                            <td>{{$payment->payment_method->name}}</td>
                        </tr>
                        <tr>
                            <td>Banco:</td>
                            <td>{{$payment->bank_name ?? ''}}</td>
                        </tr>
                        <tr>
                            <td>Tipo de Cuenta:</td>
                            <td>{{$payment->account_type ?? ''}}</td>
                        </tr>
                        <tr>
                            <td>Numero de Cuenta:</td>
                            <td>{{$payment->account_number ?? ''}}</td>
                        </tr>
                        <tr>
                            <td>Dias Trabajados:</td>
                            <td>{{$accrued->worked_days}}</td>
                        </tr>
                        <tr>
                            <td><strong>P E R I O D O</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Fecha Inicial:</td>
                            <td>{{$period->settlement_start_date}}</td>
                        </tr>
                        <tr>
                            <td>Fecha Final:</td>
                            <td>{{$period->settlement_end_date}}</td>
                        </tr>
                    </table>
                </td>
                <td class="vertical-align-top" style="width: 15%; text-align: right">
                    <img style="width: 150px;" src="{{$imageQr}}">
                </td>
            @else
                <td class="vertical-align-top" style="width: 70%;">
                    <table>
                        <tr>
                            <td>Numero Predecesor:</td>
                            <td>{{$predecessor->predecessor_number}}</td>
                        </tr>
                        <tr>
                            <td>CUNE Predecesor:</td>
                            <td>{{$predecessor->predecessor_cune}}</td>
                        </tr>
                        <tr>
                            <td>Fecha Predecesor:</td>
                            <td>{{$predecessor->predecessor_issue_date}}</td>
                        </tr>
                    </table>
                </td>
                <td class="vertical-align-top" style="width: 30%; text-align: right">
                    <img style="width: 150px;" src="{{$imageQr}}">
                </td>
            @endif
        </tr>
    </table>
    @if(isset($request->type_note) && ($request->type_note == 1))
        <table>
            <tr>
                <td>Numero Predecesor:</td>
                <td>{{$predecessor->predecessor_number}}</td>
            </tr>
            <tr>
                <td>CUNE Predecesor:</td>
                <td>{{$predecessor->predecessor_cune}}</td>
            </tr>
            <tr>
                <td>Fecha Predecesor:</td>
                <td>{{$predecessor->predecessor_issue_date}}</td>
            </tr>
        </table>
    @endif
    @if($request->type_document_id == 9 || ($request->type_document_id == 10 && $request->type_note == 1))
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Concepto</th>
                <th class="text-center">Devengados</th>
                <th class="text-center">Deducciones</th>
            </tr>
        </thead>
        <tbody>
            <?php $ItemNro = 1; ?>
            <tr>
                <td>{{$ItemNro}}</td>
                <td>INGRESO SALARIAL BASICO POR TIEMPO LABORADO</td>
                <td class="text-right">{{number_format($request['accrued']['salary'], 2)}}</td>
                <td></td>
            </tr>
            @if(isset($request['accrued']['transportation_allowance']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>SUBSIDIO DE TRANSPORTE</td>
                    <td class="text-right">{{number_format($request['accrued']['transportation_allowance'], 2)}}</td>
                    <td></td>
                </tr>
            @endif
            @if(isset($request['accrued']['HEDs']))
                @foreach($request['accrued']['HEDs'] as $HED)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HED['quantity']}} HORA(S) EXTRA(S) DIURNA(S) DESDE - {{$HED['start_time']}} HASTA {{$HED['end_time']}}</td>
                        <td class="text-right">{{number_format($HED['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['HENs']))
                @foreach($request['accrued']['HENs'] as $HEN)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HEN['quantity']}} HORA(S) EXTRA(S) NOCTURNA(S) DESDE - {{$HEN['start_time']}} HASTA {{$HEN['end_time']}}</td>
                        <td class="text-right">{{number_format($HEN['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['HRNs']))
                @foreach($request['accrued']['HRNs'] as $HRN)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HRN['quantity']}} HORA(S) EXTRA(S) RECARGO NOCTURNO DESDE - {{$HRN['start_time']}} HASTA {{$HRN['end_time']}}</td>
                        <td class="text-right">{{number_format($HRN['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['HEDDFs']))
                @foreach($request['accrued']['HEDDFs'] as $HEDDF)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HEDDF['quantity']}} HORA(S) EXTRA(S) DIURNA(S) DOMINICAL Y FESTIVO DESDE - {{$HEDDF['start_time']}} HASTA {{$HEDDF['end_time']}}</td>
                        <td class="text-right">{{number_format($HEDDF['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['HRDDFs']))
                @foreach($request['accrued']['HRDDFs'] as $HRDDF)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HRDDF['quantity']}} HORA(S) EXTRA(S) RECARGO DIURNO DOMINICAL Y FESTIVO DESDE - {{$HRDDF['start_time']}} HASTA {{$HRDDF['end_time']}}</td>
                        <td class="text-right">{{number_format($HRDDF['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['HENDFs']))
                @foreach($request['accrued']['HENDFs'] as $HENDF)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HENDF['quantity']}} HORA(S) EXTRA(S) NOCTURNA(S) DOMINICAL Y FESTIVO DESDE - {{$HENDF['start_time']}} HASTA {{$HENDF['end_time']}}</td>
                        <td class="text-right">{{number_format($HENDF['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['HRNDFs']))
                @foreach($request['accrued']['HRNDFs'] as $HRNDF)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$HRNDF['quantity']}} HORA(S) EXTRA(S) RECARGO NOCTURNO DOMINICAL Y FESTIVO DESDE - {{$HRNDF['start_time']}} HASTA {{$HRNDF['end_time']}}</td>
                        <td class="text-right">{{number_format($HRNDF['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['common_vacation']))
                @foreach($request['accrued']['common_vacation'] as $common_vacation)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$common_vacation['quantity']}} DIA(S) DE VACACIONES DESDE - {{$common_vacation['start_date']}} HASTA {{$common_vacation['end_date']}}</td>
                        <td class="text-right">{{number_format($common_vacation['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['paid_vacation']))
                @foreach($request['accrued']['paid_vacation'] as $paid_vacation)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$paid_vacation['quantity']}} DIA(S) DE VACACIONES COMPENSADAS. </td>
                        <td class="text-right">{{number_format($paid_vacation['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['service_bonus']))
                @foreach($request['accrued']['service_bonus'] as $service_bonus)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$service_bonus['quantity']}} DIA(S) DE PRIMA LEGAL</td>
                        <td class="text-right">{{number_format($service_bonus['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                    @if(array_key_exists('paymentNS', $service_bonus))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$service_bonus['quantity']}} DIA(S) DE PRIMA EXTRA LEGAL NO SALARIAL</td>
                        <td class="text-right">{{number_format($service_bonus['paymentNS'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['accrued']['severance']))
                @foreach($request['accrued']['severance'] as $severance)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE CESANTIAS</td>
                        <td class="text-right">{{number_format($severance['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE INTERESES A LAS CESANTIAS A UNA TASA DE {{$severance['percentage']}}%</td>
                        <td class="text-right">{{number_format($severance['interest_payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['work_disabilities']))
                @foreach($request['accrued']['work_disabilities'] as $work_disabilities)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$work_disabilities['quantity']}} DIA(S) DE INCAPACIDAD TIPO {{$work_disabilities['type']}} DESDE - {{$work_disabilities['start_date']}} HASTA {{$work_disabilities['end_date']}}</td>
                        <td class="text-right">{{number_format($work_disabilities['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['maternity_leave']))
                @foreach($request['accrued']['maternity_leave'] as $maternity_leave)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$maternity_leave['quantity']}} DIA(S) DE LICENCIA DE MATERNIDAD DESDE - {{$maternity_leave['start_date']}} HASTA {{$maternity_leave['end_date']}}</td>
                        <td class="text-right">{{number_format($maternity_leave['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['paid_leave']))
                @foreach($request['accrued']['paid_leave'] as $paid_leave)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$paid_leave['quantity']}} DIA(S) DE LICENCIA REMUNERADA DESDE - {{$paid_leave['start_date']}} HASTA {{$paid_leave['end_date']}}</td>
                        <td class="text-right">{{number_format($paid_leave['payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['non_paid_leave']))
                @foreach($request['accrued']['non_paid_leave'] as $non_paid_leave)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$non_paid_leave['quantity']}} DIA(S) DE LICENCIA NO REMUNERADA DESDE - {{$non_paid_leave['start_date']}} HASTA {{$non_paid_leave['end_date']}}</td>
                        <td class="text-right">{{number_format(0, 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['bonuses']))
                @foreach($request['accrued']['bonuses'] as $bonuses)
                    @if(array_key_exists('salary_bonus', $bonuses))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE BONIFICACION SALARIAL</td>
                        <td class="text-right">{{number_format($bonuses['salary_bonus'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if(array_key_exists('non_salary_bonus', $bonuses))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE BONIFICACION NO SALARIAL</td>
                        <td class="text-right">{{number_format($bonuses['non_salary_bonus'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['accrued']['aid']))
                @foreach($request['accrued']['aid'] as $aid)
                    @if(array_key_exists('salary_assistance', $aid))
                        <?php $ItemNro = $ItemNro + 1; ?>
                        <tr>
                            <td>{{$ItemNro}}</td>
                            <td>PAGO DE AUXILIO SALARIAL</td>
                            <td class="text-right">{{number_format($aid['salary_assistance'], 2)}}</td>
                            <td></td>
                        </tr>
                    @endif
                    @if(array_key_exists('non_salary_assistance', $aid))
                        <?php $ItemNro = $ItemNro + 1; ?>
                        <tr>
                            <td>{{$ItemNro}}</td>
                            <td>PAGO DE AUXILIO NO SALARIAL</td>
                            <td class="text-right">{{number_format($aid['non_salary_assistance'], 2)}}</td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['accrued']['legal_strike']))
                @foreach($request['accrued']['legal_strike'] as $legal_strike)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE {{$legal_strike['quantity']}} DIA(S) DE HUELGA LEGAL DESDE - {{$legal_strike['start_date']}} HASTA {{$legal_strike['end_date']}}</td>
                        <td class="text-right">{{number_format(0, 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['other_concepts']))
                @foreach($request['accrued']['other_concepts'] as $other_concepts)
                    @if(array_key_exists('salary_concept', $other_concepts))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>{{$other_concepts['description_concept']}} - SALARIAL</td>
                        <td class="text-right">{{number_format($other_concepts['salary_concept'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if(array_key_exists('non_salary_concept', $other_concepts))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>{{$other_concepts['description_concept']}} - NO SALARIAL</td>
                        <td class="text-right">{{number_format($other_concepts['non_salary_concept'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['accrued']['compensations']))
                @foreach($request['accrued']['compensations'] as $compensations)
                    @if(array_key_exists('ordinary_compensation', $compensations))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>COMPENSACION ORDINARIA</td>
                        <td class="text-right">{{number_format($compensations['ordinary_compensation'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if(array_key_exists('extraordinary_compensation', $compensations))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>COMPENSACION EXTRAORDINARIA</td>
                        <td class="text-right">{{number_format($compensations['extraordinary_compensation'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['accrued']['epctv_bonuses']))
                @foreach($request['accrued']['epctv_bonuses'] as $epctv_bonuses)
                    @if(array_key_exists('paymentS', $epctv_bonuses))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO SALARIAL MEDIANTE BONOS Y MEDIOS DIFERENTES A EFECTIVO O CONSIGNACION BANCARIA</td>
                        <td class="text-right">{{number_format($epctv_bonuses['paymentS'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if(array_key_exists('paymentNS', $epctv_bonuses))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO NO SALARIAL MEDIANTE BONOS Y MEDIOS DIFERENTES A EFECTIVO O CONSIGNACION BANCARIA</td>
                        <td class="text-right">{{number_format($epctv_bonuses['paymentNS'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if(array_key_exists('salary_food_payment', $epctv_bonuses))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO SALARIAL PARA ALIMENTACION MEDIANTE BONOS Y MEDIOS DIFERENTES A EFECTIVO O CONSIGNACION BANCARIA</td>
                        <td class="text-right">{{number_format($epctv_bonuses['salary_food_payment'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if(array_key_exists('non_salary_food_payment', $epctv_bonuses))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO NO SALARIAL PARA ALIMENTACION MEDIANTE BONOS Y MEDIOS DIFERENTES A EFECTIVO O CONSIGNACION BANCARIA</td>
                        <td class="text-right">{{number_format($epctv_bonuses['non_salary_food_payment'], 2)}}</td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['accrued']['commissions']))
                @foreach($request['accrued']['commissions'] as $commissions)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE COMISION</td>
                        <td class="text-right">{{number_format($commissions['commission'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['third_party_payments']))
                @foreach($request['accrued']['third_party_payments'] as $third_party_payments)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>PAGO DE TERCERAS PARTES</td>
                        <td class="text-right">{{number_format($third_party_payments['third_party_payment'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['advances']))
                @foreach($request['accrued']['advances'] as $advances)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>ANTICIPO DE NOMINA</td>
                        <td class="text-right">{{number_format($advances['advance'], 2)}}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['accrued']['endowment']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>PAGO POR DOTACIONES</td>
                    <td class="text-right">{{number_format($request['accrued']['endowment'], 2)}}</td>
                    <td></td>
                </tr>
            @endif
            @if(isset($request['accrued']['sustenance_support']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>PAGO POR APOYO A SOSTENIMIENTO</td>
                    <td class="text-right">{{number_format($request['accrued']['sustenance_support'], 2)}}</td>
                    <td></td>
                </tr>
            @endif
            @if(isset($request['accrued']['telecommuting']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>PAGO POR TELETRABAJO</td>
                    <td class="text-right">{{number_format($request['accrued']['telecommuting'], 2)}}</td>
                    <td></td>
                </tr>
            @endif
            @if(isset($request['accrued']['withdrawal_bonus']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>PAGO POR RETIRO DE LA EMPRESA</td>
                    <td class="text-right">{{number_format($request['accrued']['withdrawal_bonus'], 2)}}</td>
                    <td></td>
                </tr>
            @endif
            @if(isset($request['accrued']['compensation']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>PAGO POR INDEMNIZACION</td>
                    <td class="text-right">{{number_format($request['accrued']['compensation'], 2)}}</td>
                    <td></td>
                </tr>
            @endif
            @if(isset($request['accrued']['refund']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>PAGO POR REINTEGRO DE NOMINA</td>
                    <td class="text-right">{{number_format($request['accrued']['refund'], 2)}}</td>
                    <td></td>
                </tr>
            @endif

            @if(isset($request['deductions']['eps_deduction']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DEDUCCION CORRESPONDIENTE A SALUD POR PARTE DEL TRABAJADOR</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['eps_deduction'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['pension_deduction']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DEDUCCION CORRESPONDIENTE A PENSION POR PARTE DEL TRABAJADOR</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['pension_deduction'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['labor_union']))
                @foreach($request['deductions']['labor_union'] as $labor_union)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>DEDUCCION POR APORTE A SINDICATO DEL {{$labor_union['percentage']}}%</td>
                        <td></td>
                        <td class="text-right">{{number_format($labor_union['deduction'], 2)}}</td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['deductions']['sanctions']))
                @foreach($request['deductions']['sanctions'] as $sanction)
                    @if(array_key_exists('public_sanction', $sanction))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>DEDUCCION POR SANCION PUBLICA</td>
                        <td></td>
                        <td class="text-right">{{number_format($sanction['public_sanction'], 2)}}</td>
                    </tr>
                    @endif
                    @if(array_key_exists('private_sanction', $sanction))
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>DEDUCCION POR SANCION PRIVADA</td>
                        <td></td>
                        <td class="text-right">{{number_format($sanction['private_sanction'], 2)}}</td>
                    </tr>
                    @endif
                @endforeach
            @endif
            @if(isset($request['deductions']['orders']))
                @foreach($request['deductions']['orders'] as $order)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>DEDUCCION POR {{$order['description']}}</td>
                        <td></td>
                        <td class="text-right">{{number_format($order['deduction'], 2)}}</td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['deductions']['third_party_payments']))
                @foreach($request['deductions']['third_party_payments'] as $third_party_payment)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>DEDUCCION POR PAGO A TERCEROS</td>
                        <td></td>
                        <td class="text-right">{{number_format($third_party_payment['third_party_payment'], 2)}}</td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['deductions']['advances']))
                @foreach($request['deductions']['advances'] as $advance)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>DEDUCCION POR ANTICIPO</td>
                        <td></td>
                        <td class="text-right">{{number_format($advance['advance'], 2)}}</td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['deductions']['other_deductions']))
                @foreach($request['deductions']['other_deductions'] as $other_deduction)
                <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>{{$ItemNro}}</td>
                        <td>OTRA DEDUCCION</td>
                        <td></td>
                        <td class="text-right">{{number_format($other_deduction['other_deduction'], 2)}}</td>
                    </tr>
                @endforeach
            @endif
            @if(isset($request['deductions']['voluntary_pension']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR PENSION VOLUNTARIA</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['voluntary_pension'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['withholding_at_source']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR RETENCION EN LA FUENTE</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['withholding_at_source'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['afc']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR FOMENTO Y AHORRO A LA CONSTRUCCION</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['afc'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['cooperative']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR COOPERATIVA</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['cooperative'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['tax_liens']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR EMBARGOS FISCALES</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['tax_liens'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['supplementary_plan']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR PLAN SUPLEMENTARIO</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['supplementary_plan'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['education']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR EDUCACION</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['education'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['refund']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR REINTEGRO</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['refund'], 2)}}</td>
                </tr>
            @endif
            @if(isset($request['deductions']['debt']))
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    <td>{{$ItemNro}}</td>
                    <td>DESCUENTO POR DEUDA</td>
                    <td></td>
                    <td class="text-right">{{number_format($request['deductions']['debt'], 2)}}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <br>
    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th class="text-center"></th>
                <th class="text-center">Totales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 70%;">
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
                                <td>{{$ItemNro}}</td>
                            </tr>
                            <tr>
                                <td>Total Devengado:</td>
                                <td>{{number_format($accrued->accrued_total, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Total Deducido:</td>
                                <td>{{number_format($deductions->deductions_total, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Neto a Pagar:</td>
                                    <td>{{number_format($accrued->accrued_total - $deductions->deductions_total, 2)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <br><br>
    @endif
    <div class="summarys">
        <div class="text-word" id="note">
            <p><strong>NOTAS:</strong></p>
            <p style="font-style: italic; font-size: 9px"> {{$notes}} </p>
            <br>
        </div>
    </div>
</body>
</html>
