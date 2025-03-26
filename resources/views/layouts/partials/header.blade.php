<header class="header">
    <div class="logo-container">
        <a href="{{route('home')}}" class="logo h2">
            <i class="fas fa-home"></i>
        </a>
        <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>
    @if(Request::is('company*'))
                <span class="separator"></span>
                <div id="userbox" class="userbox mx-0 px-0">
                    <div class="profile-info">
                        @inject('model_company', 'App\Company')
                        @php
                            $current = $model_company->where('identification_number', request()->segment(2))->first();
                        @endphp
                        <span class="name text-uppercase">{{ $current->user->name }}</span>
                        <span class="role">{{ request()->segment(2) }}</span>
                    </div>
                </div>
                {{-- <span>
                    {{request()->segment(2)}}
                </span> --}}
            @endif
    @if(isset(Auth::user()->email))
        <div class="header-right">
            <span class="separator"></span>
            <div id="userbox" class="userbox">
                <a href="#" data-toggle="dropdown">
                    <figure class="profile-picture">
                        {{-- <img src="{{asset('img/%21logged-user.jpg')}}" alt="Profile" class="rounded-circle" data-lock-picture="img/%21logged-user.jpg" /> --}}
                        <div class="border rounded-circle text-center" style="width: 25px;"><i class="fas fa-user"></i></div>
                    </figure>
                    <div class="profile-info" data-lock-name="{{ Auth::user()->email }}" data-lock-email=""{{ Auth::user()->email }}">
                        <span class="name">{{ Auth::user()->name }}</span>
                        <span class="role">{{ Auth::user()->email }}</span>
                    </div>
                    <i class="fa custom-caret"></i>
                </a>
                <div class="dropdown-menu">
                    <ul class="list-unstyled mb-2">
                        <li class="divider"></li>
                        <li>
                            {{--<a role="menuitem" href="#"><i class="fas fa-user"></i> Perfil</a>--}}
                            <a role="menuitem" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-power-off"></i> Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @else
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>
                </div>
            </div>
        </nav>
    @endif
</header>
