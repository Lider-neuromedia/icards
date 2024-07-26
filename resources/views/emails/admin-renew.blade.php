@extends('emails.layout.wrapper')

@section('content')
    {{-- TODO: Traducir --}}
    <h3 style="text-align: center;">
        Clientes con suscripciones por vencer (o vencidas)
    </h3>

    @foreach ($clients as $client)
        @php
            $days = $client->getSubscriptionDaysLeft();
        @endphp

        <p style="text-align: center;">
            {{ $client->name }},

            @if ($days >= 0)
                vencerá en {{ $days }} días.
            @else
                vencío hace {{ $days * -1 }} días.
            @endif
        </p>
    @endforeach

    <p style="text-align: center;">
        <a href="{{ url('/') }}">{{ url('/') }}</a>
    </p>
@endsection
