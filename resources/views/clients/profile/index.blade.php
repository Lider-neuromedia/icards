@extends('layouts.dashboard')

@section('title', 'Perfil / ' . $client->name)

@section('breadcrumbs')
    <li class="breadcrumb-item active">Perfil</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8">

                <form action="{{ route('profile.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="id" value="{{$client->id}}">

                    @include('clients.profile.fields.name')
                    @include('clients.profile.fields.email')
                    @include('clients.profile.fields.password')

                    <div class="row">
                        <div class="col-md-12 mt-3 mb-5">
                            <button class="btn btn-primary" type="submit">
                                Actualizar Perfil
                            </button>
                        </div>
                    </div>

                    @include('clients.profile.fields.subscription')

                </form>

            </div>
        </div>

    </div>
@endsection
