@php
    $index_route = route('cards.index');
    $create_multiple_route = route('cards.store-multiple');
    $back_route = route('cards.index');
    $template_route = route('cards.template-multiple');
    
    if (
        auth()
            ->user()
            ->isAdmin()
    ) {
        $index_route = route('clients.cards.index', $client);
        $create_multiple_route = route('clients.cards.store-multiple', $client);
        $back_route = route('clients.cards.index', $client);
        $template_route = route('clients.cards.template-multiple', $client);
    }
@endphp

@extends('layouts.dashboard')

@section('title', 'Crear Multiples Tarjetas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ $index_route }}">Tarjetas</a></li>
    <li class="breadcrumb-item active">Crear Multiples Tarjetas</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-sm-8">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header text-primary">Pantilla</div>
                            <div class="card-body text-center">

                                <a class="ml-auto btn btn-primary" href="{{ $template_route }}">
                                    Descargar plantilla para llenar tarjetas
                                </a>

                                <div class="mt-3 alert alert-warning text-left">
                                    En un archivo .csv solo puede haber hasta 40 registros de tarjetas.
                                </div>

                                <div class="alert alert-warning text-left">
                                    El identificador único para que el sistema determine si debe crear una nueva tarjeta o
                                    actualizar una existente es el correo electrónico.
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ $create_multiple_route }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header text-primary">Subir archivo</div>
                                <div class="card-body">

                                    @include('clients.cards.fields.csvfile')

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 my-5">
                            <a class="btn btn-dark" href="{{ $back_route }}">Volver</a>
                            <button class="btn btn-primary" type="submit">
                                Guardar
                            </button>
                        </div>
                    </div>


                </form>

            </div>
        </div>

    </div>
@endsection
