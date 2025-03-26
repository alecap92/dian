@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{$company->name}}
        @endcomponent
    @endslot
    
    # {{$document->type_document->name}} {{$document->prefix}}-{{$document->consecutive}}
    
    Hola, enviamos la representación grafica de tu {{$document->type_document->name}} {{$document->prefix}}-{{$document->consecutive}}.
    
    @slot('subcopy')
        Gracias,<br>
        {{$company->name}}
    @endslot
    
    @slot('footer')
        @component('mail::footer')
            © {{date('Y')}} {{$company->name}}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent

