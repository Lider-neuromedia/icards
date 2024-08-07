<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        rel="shortcut icon"
        href="{{ url('favicon.ico') }}"
        type="image/x-icon"
    >
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TODO: traer de .env --}}
    <title>iCard</title>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <style>
        @media screen and (min-width: 600px) {
            main {
                left: 50%;
                position: fixed;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 100%;
            }
        }
    </style>
</head>

<body class="page @yield('body-class')">
    <main class="mt-3 mt-sm-0">
        <div class="container">
            @include('partials.messages')
        </div>
        <div class="container">
            @yield('content')
        </div>
    </main>
</body>

</html>
