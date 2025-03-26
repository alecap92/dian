@extends('layouts.app')
@section('title', 'Contact')
@section('content')
    <div class="container col-xs-8 col-xs-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Documentos enviados al adquiriente - {{$customer_idnumber}}.</h2>
            </div>
            @if ($documents->isEmpty())
                <div>No hay documentos para mostrar.</div>
            @else
                <table class="table table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">Tipo Documento</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Prefijo</th>
                            <th scope="col">Numero</th>
                            <th scope="col">XML</th>
                            <th scope="col">PDF</th>
                            <th scope="col">AttachedDocument</th>
                            <th scope="col">ZipAtt</th>
                            <th scope="col">Enviar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr class="table-light">
                                <td>{!! $document->type_document->name !!}</td>
                                <td>{!! $document->date_issue !!}</td>
                                <td>{!! $document->prefix !!}</td>
                                <td>{!! $document->number !!}</td>
                                @php
                                    $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true)
                                @endphp
                                @if($allow_public_downloads)
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->xml) }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->pdf) }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/Attachment-'.$document->prefix.$document->number.'.xml') }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/ZipAttachm-'.$document->prefix.$document->number.'.xml') }}"><i class="fa fa-download"></i></a></td>
                                    <td><form action="{{ route('send-email-customer') }}" method="POST">
                                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
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
                                    <td><form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="Attachment-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td><form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="ZipAttachm-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td><form action="{{ route('send-email-customer') }}" method="POST">
                                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
                                            <button type="submit" class="fa fa-envelope"></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
