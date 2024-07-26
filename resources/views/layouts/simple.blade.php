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
    <meta name="url-root" content="{{ url('/') }}">
    <meta name="description" content="@yield('meta-description', '')">

    @yield('meta')

    {{-- TODO: iCard traer de .env --}}
    <title>@yield('title', 'iCard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >
    <link href="https://fonts.googleapis.com/css2?family=Exo:wght@100;300;400;700&display=swap" rel="stylesheet">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css"
        integrity="sha512-NhSC1YmyruXifcj/KFRWoC561YpHpc5Jtzgvbuzx5VozKpWvQ+4nXhPdFgmx8xqexRcpAglTj9sIBWINXa8x5w=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

    @yield('styles')
</head>

<body>
    <div class="container">
        @include('partials.messages')
    </div>
    @yield('content')

    <script src="{{ mix('js/public.js') }}"></script>
</body>

</html>
