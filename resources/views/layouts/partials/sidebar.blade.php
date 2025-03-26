@php
$path = explode('/', request()->path());
$path[1] = (array_key_exists(1, $path)> 0)?$path[1]:'';
$path[2] = (array_key_exists(2, $path)> 0)?$path[2]:'';
$path[0] = ($path[0] === '')?'documents':$path[0];
$comp_id = $path[1];
$cust_id = $path[2];
@endphp

<aside id="sidebar-left" class="sidebar-left">
    <div class="sidebar-header">
        <div class="sidebar-title" style="padding-left: 30px;">
            Menu
        </div>
        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>
    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">
                    @if(!Request::is('company*'))
                    <li class="{{ ($path[0] === 'home')?'nav-active':'' }}">
                        <a class="nav-link" href="{{route('home')}}">
                            <i class="fas fa-industry" aria-hidden="true"></i>
                            <span>Empresas</span>
                        </a>
                    </li>
                    @endif
                    @if(Request::is('company*'))
                        <li class="{{ Route::is('company') ? 'nav-active' : '' }}">
                            <a class="nav-link" href="{{route('company', request()->segment(2))}}">
                                <i class="fas fa-list-alt" aria-hidden="true"></i>
                                <span>Listado de documentos</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('company.events') ? 'nav-active' : '' }}">
                            <a class="nav-link" href="{{route('company.events', request()->segment(2))}}">
                                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                                <span>Eventos RADIAN</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('company.payrolls') ? 'nav-active' : '' }}">
                            <a class="nav-link" href="{{route('company.payrolls', request()->segment(2))}}">
                                <i class="fas fa-address-book" aria-hidden="true"></i>
                                <span>Nominas emitidas</span>
                            </a>
                        </li>
                    @endif
                    @if(!Request::is('company*'))
                    <li class="">
                        <a href="{{route('documentation')}}" class="nav-link" target="BLANK">
                            <i class="fa fa-server" aria-hidden="true"></i>
                            <span>Test API SWAGGER</span>
                        </a>
                    </li>
                    @endif

                    {{-- @if(isset(Auth::user()->email))
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'' }}">
                            <a class="nav-link" href="{{route('documents_index')}}">
                                <i class="fas fa-receipt" aria-hidden="true"></i>
                                <span>Documentos</span>
                            </a>
                        </li>
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'' }}">
                                <a class="nav-link" href="{{route('tax_index')}}">
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Impuestos</span>
                                </a>
                            </li>
                        <li class="nav-parent {{ in_array($path[0], ['items_ecommerce', 'tags', 'promotions'])?'nav-active nav-expanded':'' }}">
                            <a class="nav-link" href="#">
                                <i class="fas fa-receipt" aria-hidden="true"></i>
                                <span>Empresa</span>
                            </a>
                            <ul class="nav nav-children">
                                <li class="{{ ($path[0] === 'items_ecommerce')?'nav-active':'' }}">
                                    <a class="nav-link" href="{{route('configuration_index')}}">
                                        Lista
                                    </a>
                                </li>
                                <li class="{{ ($path[0] === 'tags')?'nav-active':'' }}">
                                    <a class="nav-link" href="{{route('configuration_admin')}}">
                                        Nuevo
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'nav-active' }}">
                            <form action="{{ url('/okcustomerlogin/'.$comp_id.'/'.$cust_id) }}" method="POST">
                                @csrf
                                <a class="nav-link" href="javascript:;" onclick="$('#action-button').click();">
                                    <input type="hidden" name="verificar" value="FALSE"/>
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Documentos Recibidos</span>
                                </a>
                                <input type="submit" id="action-button" style="display: none;" >
                            </form>
                        </li>
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'nav-active' }}">
                            <form action="{{ url('/customer-password/'.$comp_id.'/'.$cust_id) }}" method="GET">
                                @csrf
                                <a class="nav-link" href="javascript:;" onclick="$('#action-button2').click();">
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Cambiar Password Cliente</span>
                                </a>
                                <input type="submit" id="action-button2" style="display: none;" >
                            </form>
                        </li>
                    @endif --}}
                </ul>
            </nav>
        </div>
        <script>
            // Maintain Scroll Position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');
                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>
    </div>
</aside>
