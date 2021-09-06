@extends('layouts.dashboard')

@section('title', 'Crear Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('clients.index')}}">Clientes</a></li>
    <li class="breadcrumb-item active">Crear Cliente</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8">

                <form action="{{ route('clients.store', $client) }}" method="post">
                    @csrf
                    @include('admin.clients.form')
                </form>

            </div>
        </div>

    </div>
@endsection
