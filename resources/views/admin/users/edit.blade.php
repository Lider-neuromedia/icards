@extends('layouts.dashboard')

@section('title', 'Editar Usuario')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('users.index')}}">Usuarios</a></li>
    <li class="breadcrumb-item active">Editar Usuario</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8">

                {{-- Formulario de editar --}}

                <form action="{{ route('users.update', $user) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{$user->id}}">
                    @include('admin.users.form')
                </form>

                {{-- Formulario de borrar --}}

                @if (auth()->user()->id == $user->id)

                    <div class="alert alert-warning text-center">
                        No se puede borrar así mismo
                    </div>

                @else

                    @include('partials.delete', [
                        'id_form' => 'delete-user-form',
                        'label' => 'Borrar Usuario',
                        'route' => route('users.destroy', $user->id)
                    ])

                @endif

            </div>
        </div>

    </div>
@endsection
