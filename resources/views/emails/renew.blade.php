@extends('emails.layout.wrapper')

@section('content')
    <h1 style="text-align: center;">Aviso de Vencimiento</h1>
    <p style="text-align: center;">Su suscripción a iCard vencerá en {{$days}} días.</p>
@endsection