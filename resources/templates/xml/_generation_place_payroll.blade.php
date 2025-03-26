<LugarGeneracionXML
    Pais="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->code)}}"
    DepartamentoEstado="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}"
    MunicipioCiudad="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}"
    Idioma="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->language->code)}}"></LugarGeneracionXML>

