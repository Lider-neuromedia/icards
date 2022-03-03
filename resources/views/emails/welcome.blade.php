@extends('emails.layout.wrapper')

@section('content')
    <h1 style="text-align: center;">
        {{$name}}, Le damos la bienvenida a iCard
    </h1>

    <p style="text-align: center;">
        Ya puedes ir a <a href="{{url('/login')}}">{{url('/login')}}</a> para ingresar tus tarjetas de presentación.
    </p>

    <p style="text-align: center;">
        <strong>Accesos:</strong><br/>
        {{$credentials['email']}}<br/>
        {{$credentials['password']}}
    </p>
@endsection