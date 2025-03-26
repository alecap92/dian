@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        Nominas y ajustes
    </div>
    <div class="card-body p-0">
        @include('partials.payrolls.table')
    </div>
</div>
@endsection

@push('scripts')
<script>
</script>
@endpush
