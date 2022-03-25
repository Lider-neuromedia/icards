@extends('emails.layout.wrapper')

@section('content')

    <p style="text-align: center;">
        Hola {{$client->name}} tu membresía iCard está próxima a vencerse.<br>
        Para renovarla escríbenos a <a href="tel:+573006799959">300 679 99 59</a> - <a href="tel:+573168702492">316 870 24 92</a> para brindarte soporte.<br>
    </p>

    <p style="text-align: center;">
        @if ($days >= 0)
            Tu suscripción a iCard vencerá en {{$days}} días.<br>
        @else
            Tu suscripción a iCard vencío hace {{$days * - 1}} días.<br>
        @endif

        Puedes ver tu perfil en <a href="{{url('/clients/profile')}}">{{url('/clients/profile')}}</a>
    </p>

    <p style="text-align: center;">
        iCard<br>
        Una nueva forma de presentarte al mundo.
    </p>

@endsection