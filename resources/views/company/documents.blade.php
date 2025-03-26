@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        Listado de documentos generados
    </div>
    <div class="table-responsive card-body p-0">
        <table class="table table-sm table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Acciones</th>
                    <th>Descargas</th>
                    <th>Ambiente</th>
                    <th>DIAN</th>
                    <th>Fecha</th>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Tipo de Documento</th>
                    <th class="text-right">Impuesto</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $row)
                    <tr class="table-light">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($row->response_dian)
                                <button type="button" class="btn btn-primary btn-xs modalApiResponse"
                                    data-content="{{ $row->response_dian }}">
                                    Respuesta DIAN
                                </button>
                                <br>
                            @endif
                            @if($row->cufe)
                                <button type="button" class="btn btn-primary btn-xs makeApiRequest mt-1"
                                    data-id="{{ $row->cufe }}">
                                    CUFE
                                </button>
                                <br>
                            @endif
                            @if(!$row->state_document_id)
                                <button type="button" class="btn btn-primary btn-xs modalChangeState mt-1"
                                    data-id="{{ $row->id }}">
                                    ESTADO
                                </button>
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-success btn-xs text-white"
                                role="button"
                                href="{{ '/storage/'.$row->identification_number.'/'.$row->xml }}" target="_BLANK">
                                XML
                            </a>
                            <a class="btn btn-success btn-xs text-white mt-1"
                                role="button"
                                href="{{ '/storage/'.$row->identification_number.'/'.$row->pdf }}" target="_BLANK">
                                PDF
                            </a>
                        </td>
                        <td>{{ $row->ambient_id === 2 ? 'Habilitación' : 'Producción' }}</td>
                        <td class="text-center">{{ $row->state_document_id ? 'Si' : 'No' }}</td>
                        <td>{{ $row->date_issue }}</td>
                        <td>{{ $row->prefix }}{{ $row->number }}</td>
                        <td>
                            @inject('typeDocuments', 'App\TypeDocumentIdentification')
                            @php
                                $doc_id = $row->client->type_document_identification_id ?? null;
                                $document_type = $typeDocuments->where('id', $doc_id)->first() ?? null;
                                // dd($document_type);
                            @endphp
                            {{-- @if(!$document_type)
                                {{dd($row->client)}}
                            @endif --}}
                            {{ $row->client->name ?? 'Sin nombre' }}<br>
                            {{ $document_type != null ? $document_type->name : '' }} {{ $row->client->identification_number ?? 'sin identificación' }}-{{ $row->client->dv ?? ""}}</td>
                        <td>{{ $row->type_document->name }}</td>
                        <td class="text-right">{{ round($row->total_tax, 2) }}</td>
                        <td class="text-right">{{ round($row->subtotal, 2) }}</td>
                        <td class="text-right">{{ round($row->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-center">
        {{ $documents->links() }}
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalLabel">Consulta de CUFE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyContent"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Respuesta dada por el API</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyResponse"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="changeStateModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Cambio de Estado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">Esto cambiará el estado del documento en este listado del API, es importante que se verifique el <strong>CUFE</strong> en la DIAN donde se muestre como ACEPTADO para continuar con este procedimiento.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <form action="{{ route('document.change-state') }}" method="POST">
                    @csrf
                    <input type="hidden" name="document_id" id="verificarInput" value=""/>
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.makeApiRequest').click(function() {
        var cufe = $(this).data('id');
        var $button = $(this);
        $button.prop('disabled', true);

        $.ajax({
            url: '{{ url('/company/'.$company->identification_number.'/document/') }}/' + cufe,
            method: 'GET',
            success: function(response) {
                // Mostrar la respuesta en el modal
                $('#modalBodyContent').html(JSON.stringify(response, null, 2));
                $('#resultModal').modal('show');
            },
            error: function(xhr) {
                // Manejar errores
                $('#modalBodyContent').html('Ocurrió un error: ' + xhr.status + ' ' + xhr.statusText);
                $('#resultModal').modal('show');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    $('.modalApiResponse').click(function() {
        var content = $(this).data('content');
        $('#modalBodyResponse').html(JSON.stringify(content, null, 2));
        $('#responseModal').modal('show');
    });
    $('.modalChangeState').click(function() {
        var id = $(this).data('id');
        $('#verificarInput').val(id);
        $('#changeStateModal').modal('show');
    });
});
</script>
@endpush
