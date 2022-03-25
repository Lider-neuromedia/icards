@extends('emails.layout.wrapper')

@section('content')
    <p style="text-align: center;">
        Hola {{$name}} ahora haces parte de la era digital.<br>
        Aquí encontrarás tu usuario y contraseña para activar tu iCard.<br>
    </p>

    <p style="text-align: center;">
        <strong>Accesos:</strong><br/>
        <strong>URL:</strong> <a href="{{url('/login')}}">{{url('/login')}}</a><br>
        <strong>Correo:</strong> {{$credentials['email']}}<br/>
        <strong>Contraseña:</strong> {{$credentials['password']}}
    </p>

    <p style="text-align: center;">
        iCard<br>
        Una nueva forma de presentarte al mundo.
    </p>
@endsection