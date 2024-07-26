@extends('emails.layout.wrapper')

@section('content')
    {{-- TODO: Traducir --}}
    <p style="text-align: center;">
        ¡Felicitaciones!<br>
        Ya está disponible tu iCard.<br>
        Para verla ingresa en este enlace<br>
        <a href="{{ $url }}">{{ $url }}</a>
    </p>

    <p style="text-align: center;">
        iCard<br>
        Una nueva forma de presentarte al mundo.
    </p>
@endsection
