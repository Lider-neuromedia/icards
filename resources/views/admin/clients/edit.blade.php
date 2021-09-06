@extends('layouts.dashboard')

@section('title', 'Editar Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('clients.index')}}">Clientes</a></li>
    <li class="breadcrumb-item active">Editar Cliente</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8">

                {{-- Formulario de editar --}}

                <form action="{{ route('clients.update', $client) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{$client->id}}">
                    @include('admin.clients.form')
                </form>

                {{-- Formulario de borrar --}}

                @include('partials.delete', [
                    'id_form' => 'delete-client-form',
                    'label' => 'Borrar Cliente',
                    'route' => route('clients.destroy', $client->id)
                ])

            </div>
        </div>

    </div>
@endsection
