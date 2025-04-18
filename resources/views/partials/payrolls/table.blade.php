@if ($documents->isEmpty())
    <div>No hay documentos para mostrar.</div>
@else
    @if(!Request::is('company*'))
    <form method="GET" action="{{ url('/oksellerspayrollssearch/'.$company_idnumber) }}">
        <table class="table">
            <tr>
                <td>
                    <div>
                        <select id="searchfield" name="searchfield" class="browser-default custom-select">
                            <option selected="">Seleccione campo para filtrar.</option>
                            <option value="9">Nomina Individual: Numero</option>
                            <option value="10">Nomina Individual de Ajuste: Numero</option>
                            <option value="3">Fecha</option>
                            <option value="4">ID Empleado</option>
                            <option value="5">Prefijo</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div>
                        <input id="searchvalue" type="text" class="form-control" name="searchvalue" autofocus>
                    </div>
                </td>
                <td>
                    <div>
                        <button type="submit" id="btnsearch" name="btnsearch" class="btn btn-primary">
                            Buscar
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
    @endif
    <div class="table-responsive">
        <table class="table table-sm">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Tipo Documento</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Prefijo</th>
                    <th scope="col">Numero</th>
                    <th scope="col">ID Empleado</th>
                    <th scope="col">XML</th>
                    <th scope="col">PDF</th>
                    <th scope="col">Enviar</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $document)
                    <tr class="table-light">
                        <td>{!! $document->type_document->name !!}</td>
                        <td>{!! $document->date_issue !!}</td>
                        <td>{!! $document->prefix !!}</td>
                        <td>{!! $document->consecutive !!}</td>
                        <td>{!! $document->employee_id !!}</td>
                        @php
                            $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true)
                        @endphp
                        @if($allow_public_downloads)
                            <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->xml) }}"><i class="fa fa-download"></i></a></td>
                            <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->pdf) }}"><i class="fa fa-download"></i></a></td>
                            <td><a href="{{ url('/api/download/'.$company_idnumber.'/Attachment-'.$document->prefix.$document->consecutive.'.xml') }}"><i class="fa fa-download"></i></a></td>
                            <td><a href="{{ url('/api/download/'.$company_idnumber.'/ZipAttachm-'.$document->prefix.$document->consecutive.'.xml') }}"><i class="fa fa-download"></i></a></td>
                            <td><form action="{{ route('send-email-customer') }}" method="POST">
                                    <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                    <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                    <input type="hidden" name="number" value="{{$document->consecutive}}">
                                    <button type="submit" class="fa fa-envelope"></button>
                                </form>
                            </td>
                        @else
                            <td><form action="{{ route('downloadfile') }}" method="POST">
                                    <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                    <input type="hidden" name="file" value="{{$document->xml}}">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="fa fa-download"></button>
                                </form>
                            </td>
                            <td><form action="{{ route('downloadfile') }}" method="POST">
                                    <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                    <input type="hidden" name="file" value="{{$document->pdf}}">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="fa fa-download"></button>
                                </form>
                            </td>
                            <td><form action="{{ route('send-email-employee') }}" method="POST">
                                    <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                    <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                    <input type="hidden" name="number" value="{{$document->consecutive}}">
                                    <button type="submit" class="fa fa-envelope"></button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <div>
                {!! $documents->appends(request()->query())->links() !!}
            </div>
        </table>
    </div>
@endif