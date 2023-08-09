@php
    $index_route = route('cards.index');
    $create_route = route('cards.store', $card);
    
    if (isUserAdmin()) {
        $index_route = route('clients.cards.index', $client);
        $create_route = route('clients.cards.store', [$client, $card]);
    }
@endphp

@extends('layouts.dashboard')

@section('title', 'Crear Tarjeta')

@section('breadcrumbs')
    @if (isUserAdmin())
        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clientes</a></li>
    @endif
    <li class="breadcrumb-item"><a href="{{ $index_route }}">Tarjetas</a></li>
    <li class="breadcrumb-item active">Crear Tarjeta</li>
@endsection

@section('pre-scripts')
    <script>
        window.groups = @json($groups);
    </script>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-sm-8">

                <form action="{{ $create_route }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @include('clients.cards.form')
                </form>

            </div>
        </div>

    </div>
@endsection
