@extends('layouts.dashboard')

@section('title', 'Usuarios')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>

                    <div class="card-tools">
                        <form action="{{route('users.index')}}" method="get">
                            <div class="input-group input-group-sm" style="max-width: 300px;">
                                <input value="{{$search}}" type="text" name="search" class="form-control float-right" placeholder="Buscar">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table text-nowrap">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>E-mail</th>
                                <th>Rol</th>
                                <th class="text-right">
                                    <a href="{{route('users.create')}}" class="btn btn-primary btn-xs" title="Crear Usuario">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($users as $user)
                                <tr>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->role_description}}</td>
                                    <td class="text-right">
                                        <a class="btn btn-xs btn-success" href="{{route('users.edit', $user->id)}}" title="Editar Usuario">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    {{$users->appends(['search' => $search])->links()}}
                </div>
            </div>

        </div>
    </div>

@endsection
