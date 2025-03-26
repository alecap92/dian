@extends('layouts.app', ['is_seller' => true])
@section('title', 'Contact')
@section('content')
    <div class="container col-xs-8 col-xs-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Nominas y ajustes emitidos por la empresa - {{ $company_idnumber}}.</h2>
            </div>
            @include('partials.payrolls.table')
        </div>
    </div>
@endsection
