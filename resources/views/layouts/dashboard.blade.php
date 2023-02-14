<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Icard')</title>

    <!-- Scripts -->
    @yield('pre-scripts')
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Google Font: Source Sans Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,300;0,400;0,700;1,400&display=swap">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-none d-sm-inline-block">
                    <a class="nav-link" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-link text-center">
                <span class="brand-text font-weight-light">{{ \Auth::user()->name }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        @if (auth()->user()->isClient())
                            <li class="nav-item">
                                <a href="{{ route('cards.index') }}" class="nav-link">
                                    <i class="nav-icon far fa-address-card"></i>
                                    <p>Tarjetas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('profile.index') }}" class="nav-link">
                                    <i class="nav-icon far fa-user-circle"></i>
                                    <p>Perfil</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cards.theme') }}" class="nav-link">
                                    <i class="nav-icon far fa fa-palette"></i>
                                    <p>Tema</p>
                                </a>
                            </li>
                        @endif

                        @if (auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('clients.index') }}" class="nav-link">
                                    <i class="nav-icon far fa-address-card"></i>
                                    <p>Clientes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link">
                                    <i class="nav-icon far fa-user-circle"></i>
                                    <p>Administradores</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('sellers.index') }}" class="nav-link">
                                    <i class="nav-icon far fa-user-circle"></i>
                                    <p>Vendedores</p>
                                </a>
                            </li>
                        @endif

                        <li class="nav-item border-top">
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="nav-link">
                                <i class="nav-icon far fa fa-door-closed"></i>
                                <p>Cerrar Sesión</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('title', 'iCard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumbs')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container">
                    @include('partials.messages')
                </div>
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
            <!-- /.content -->

        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer text-right">
            Desarrollador por <strong><a href="https://neuromedia.com" target="_blank">NeuroMedia</a>.</strong>
        </footer>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</body>

</html>
