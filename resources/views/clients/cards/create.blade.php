@extends('layouts.dashboard')

@section('title', 'Crear Tarjeta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('cards.index')}}">Tarjetas</a></li>
    <li class="breadcrumb-item active">Crear Tarjeta</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12">

                <form action="{{ route('cards.store', $card) }}" method="post">
                    @csrf
                    @include('clients.cards.form')
                </form>

            </div>
        </div>

    </div>
@endsection
