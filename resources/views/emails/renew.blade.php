@extends('emails.layout.wrapper')

@section('content')
    <h3 style="text-align: center;">
        {{$client->name}}, usted tiene una notificación de vencimiento
    </h3>

    @if ($days >= 0)
        <p style="text-align: center;">Su suscripción a iCard vencerá en {{$days}} días.</p>
    @else
        <p style="text-align: center;">Su suscripción a iCard vencío hace {{$days * - 1}} días.</p>
    @endif

    <p style="text-align: center;">
        Puede ver su perfil en
        <a href="{{url('/clients/profile')}}">{{url('/clients/profile')}}</a>
    </p>
@endsection