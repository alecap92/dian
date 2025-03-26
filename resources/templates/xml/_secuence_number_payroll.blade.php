<NumeroSecuenciaXML
    @isset($request->worker_code)
        CodigoTrabajador="{{preg_replace("/[\r\n|\n|\r]+/", "", $request->worker_code)}}"
    @endisset
    Prefijo="{{preg_replace("/[\r\n|\n|\r]+/", "", $request->prefix)}}"
    Consecutivo="{{preg_replace("/[\r\n|\n|\r]+/", "", $request->consecutive)}}"
    Numero="{{preg_replace("/[\r\n|\n|\r]+/", "", $request->prefix.$request->consecutive)}}"></NumeroSecuenciaXML>
