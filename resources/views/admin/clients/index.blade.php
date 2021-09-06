@extends('layouts.dashboard')

@section('title', 'Clientes')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Clientes</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>

                    <div class="card-tools">
                        <form action="{{route('clients.index')}}" method="get">
                            <div class="input-group input-group-sm" style="max-width: 300px;">
                                <input value="{{$search}}" type="search" name="search" class="form-control float-right" placeholder="Buscar">
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
                                <th title="Fecha de vencimiento">Suscripción</th>
                                <th class="text-center">Tarjetas</th>
                                <th class="text-right">
                                    <a href="{{route('clients.create')}}" class="btn btn-primary btn-xs" title="Crear Cliente">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($clients as $client)
                                <tr>
                                    <td>{{$client->name}}</td>
                                    <td>{{$client->email}}</td>
                                    <td>{{$client->subscription_status}}</td>
                                    <td class="text-center">{{$client->cards_usage}}</td>
                                    <td class="text-right">
                                        <a class="btn btn-xs btn-success" href="{{route('clients.edit', $client->id)}}" title="Editar Cliente">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    {{$clients->appends(['search' => $search])->links()}}
                </div>
            </div>

        </div>
    </div>

@endsection
