@extends('layouts.dashboard')

@section('title', 'Crear Usuario')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('users.index')}}">Usuarios</a></li>
    <li class="breadcrumb-item active">Crear Usuario</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8">

                <form action="{{ route('users.store', $user) }}" method="post">
                    @csrf
                    @include('admin.users.form')
                </form>

            </div>
        </div>

    </div>
@endsection
