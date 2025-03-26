@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        Eventos RADIAN
    </div>
    <div class="card-body p-0">
        @include('partials.events.table')
    </div>
</div>
@endsection

@push('scripts')
<script>
</script>
@endpush
