@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        Empresas
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Documentos generados</th>
                    <th>Nombre de usuario</th>
                    <th>Correo de usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($companies as $row)
                    <tr class="table-light">
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->identification_number }}-{{ $row->dv }}</td>
                        <td>{{ $row->total_documents }}</td>
                        <td>{{ $row->user->name }}</td>
                        <td>{{ $row->user->email }}</td>
                        <td>
                            <a class="btn btn-primary text-white btn-sm" href="{{ route('company', $row->identification_number)}}">Ver documentos</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
