<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Notificacion de Comprobante de Nomina Electronica Nro {{$payroll[0]->prefix}}-{{$payroll[0]->consecutive}}</title>
</head>
<body>
    <p>Señor(a),</p>
    <p>{{$employee->first_name}} {{$employee->middlename}} {{$employee->surname}} {{$employee->second_surname}}
    @if(isset($employee->identification_number))
        <p>Numero Id. {{$employee->identification_number}}
    @else
        <p>Numero Id. {{$employee->company->identification_number}}
    @endif
    <p><p>
    <p>Le informamos ha recibido un documento de nomina electronica de {{$company->user->name}}.</p>
    <p></p>
    <p>Número de documento: {{$payroll[0]->prefix}}{{$payroll[0]->consecutive}}</p>
    <p>Fecha de emisión: {{$payroll[0]->created_at}}</p>
    <p>Valor: {{number_format($payroll[0]->total_payroll, 2)}}</p>
    <p>Si requiere consultar el documento en nuestro sitio por favor ingrese a: </p>
    @if(isset($employee->identification_number))
        <a href="{{config('app.url')}}/employeelogin/{{$company->identification_number}}/{{$employee->identification_number}}">{{env('APP_URL')}}/employeelogin/{{$company->identification_number}}/{{$employee->identification_number}}</a>
    @else
        <a href="{{config('app.url')}}/employeelogin/{{$company->identification_number}}/{{$employee->company->identification_number}}">{{env('APP_URL')}}/employeelogin/{{$company->identification_number}}/{{$employee->company->identification_number}}</a>
    @endif
    <p></p>
    <p>---------------------------------------------------------------------------------------------------------------------------</p>
    <p>Este es un sistema automático de aviso, por favor no responda este mensaje al correo.</p>
    <p>---------------------------------------------------------------------------------------------------------------------------</p>
</body>
</html>
