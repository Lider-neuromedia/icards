@extends('layouts.dashboard')

@section('title', 'Editar Tarjeta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('cards.index')}}">Tarjetas</a></li>
    <li class="breadcrumb-item active">Editar Tarjeta</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-sm-8">

                <div class="card mb-5">
                    <div class="card-header">
                        <div class="card-title">Ver Tarjeta</div>
                        <div class="card-tools">
                            <a class="btn btn-primary btn-sm" title="Ver Tarjeta" href="{{$card->url}}" target="_blank">
                                <i class="fa fa-link" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Formulario de editar --}}

                <form action="{{ route('cards.update', $card) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{$card->id}}">
                    @include('clients.cards.form')
                </form>

                <div class="card mb-5">
                    <div class="card-header">
                        <div class="card-title">Ver Tarjeta</div>
                        <div class="card-tools">
                            <a class="btn btn-primary btn-sm" title="Ver Tarjeta" href="{{$card->url}}" target="_blank">
                                <i class="fa fa-link" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Formulario de borrar --}}

                @include('partials.delete', [
                    'id_form' => 'delete-card-form',
                    'label' => 'Borrar Tarjeta',
                    'route' => route('cards.destroy', $card->id)
                ])

            </div>
        </div>

    </div>
@endsection
