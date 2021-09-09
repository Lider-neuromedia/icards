@extends('emails.layout.wrapper')

@section('content')
    <h1 style="text-align: center;">
        {{$name}}, Bienvenido a iCard
    </h1>
    <p style="text-align: center;">
        Ya puedes ir a <a href="{{url('/login')}}">{{url('/login')}}</a> para ingresar tus tarjetas de presentación.
    </p>
@endsection