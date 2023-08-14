@php
    $index_route = route('cards.index');
    $edit_route = route('cards.update', $card);
    $destroy_route = route('cards.destroy', $card);
    $theme_route = route('cards.theme');
    
    if (isUserAdmin()) {
        $index_route = route('clients.cards.index', $client);
        $edit_route = route('clients.cards.update', [$client, $card]);
        $destroy_route = route('clients.cards.destroy', [$client, $card]);
        $theme_route = route('clients.cards.theme', $client);
    }
@endphp

@extends('layouts.dashboard')

@section('title', 'Editar Tarjeta')

@section('breadcrumbs')
    @if (isUserAdmin())
        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clientes</a></li>
    @endif
    <li class="breadcrumb-item"><a href="{{ $index_route }}">Tarjetas</a></li>
    <li class="breadcrumb-item active">Editar Tarjeta</li>
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

                <div class="card mb-5">
                    <div class="card-header">
                        <div class="card-title"></div>
                        <div class="card-tools">
                            <a class="btn btn-primary btn-sm" title="Ver Tarjeta" href="{{ $card->url }}"
                                target="_blank">
                                <i class="fa fa-link" aria-hidden="true"></i>
                                <span class="ml-1">Ver Tarjeta</span>
                            </a>
                            <a class="btn btn-primary btn-sm" title="Ver Tarjeta" href="{{ $theme_route }}"
                                target="_blank">
                                <i class="fa fa-palette" aria-hidden="true"></i>
                                <span class="ml-1">Editar Tema</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Formulario de editar --}}

                <form action="{{ $edit_route }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{ $card->id }}">

                    @if (isUserClient() && auth()->user()->id != $card->client_id)
                        <input type="hidden" name="account" value="{{ $card->client_id }}" />
                    @endif

                    @include('clients.cards.form')
                </form>

                <div class="card mb-5">
                    <div class="card-header">
                        <div class="card-title">Ver Tarjeta</div>
                        <div class="card-tools">
                            <a class="btn btn-primary btn-sm" title="Ver Tarjeta" href="{{ $card->url }}"
                                target="_blank">
                                <i class="fa fa-link" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Formulario de borrar --}}

                @include('partials.delete', [
                    'id_form' => 'delete-card-form',
                    'label' => 'Borrar Tarjeta',
                    'route' => $destroy_route,
                ])

            </div>
        </div>

    </div>
@endsection
