<footer id="footer" margin-top:100px>
    <hr style="margin-bottom: 4px;">
    <p id='mi-texto'>Comprobante de Nomina No: {{$resolution->prefix}} - {{$request->consecutive}} - Fecha y Hora de Generación: {{$period->issue_date}} - {{Carbon\Carbon::now()->format('H:i:s')}}<br> CUNE: <strong>{{$cufecude}}</strong></p>
    @isset($request->foot_note)
        <p id='mi-texto-1'><strong>{{$request->foot_note}}</strong></p>
    @endisset
</footer>
