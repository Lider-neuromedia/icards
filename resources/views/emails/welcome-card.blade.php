@extends('emails.layout.wrapper')

@section('content')
    <h1 style="text-align: center;">
        Hola {{$name}}, su tarjeta de presentación iCard ha sido creada
    </h1>

    <p style="text-align: center;">
        Puede ver su tarjeta en <a href="{{$url}}">{{$url}}</a>.
    </p>
@endsection