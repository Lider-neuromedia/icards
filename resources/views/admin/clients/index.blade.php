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
                        <form action="{{ route('clients.index') }}" method="get">
                            <div class="input-group input-group-sm" style="max-width: 300px;">
                                <input value="{{ $search }}" type="search" name="search"
                                    class="form-control float-right" placeholder="Buscar">
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
                                <th>Vendedor</th>
                                <th>Cuenta</th>
                                <th>Cuentas Habilitadas</th>
                                <th title="Fecha de vencimiento">Suscripción</th>
                                <th class="text-center">
                                    {{-- Tarjetas --}}
                                    <i class="nav-icon far fa-address-card"></i>
                                </th>
                                <th class="text-center">
                                    <i class="nav-icon far fa-edit"></i>
                                </th>
                                <th class="text-center">
                                    <i class="nav-icon far fa-list-alt"></i>
                                </th>
                                <th class="text-right">
                                    <a href="{{ route('clients.create') }}" class="btn btn-primary btn-xs"
                                        title="Crear Cliente">
                                        Crear Cliente
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($clients as $client)
                                <tr>
                                    <td>{{ $client->seller_name }}</td>
                                    <td>
                                        <div>{{ $client->name }}</div>
                                        <div class="text-gray">{{ $client->email }}</div>
                                    </td>
                                    <td>
                                        <div class="text-gray">Cuentas: {{ $client->allowedAccounts->count() }}</div>
                                        <ul class="pl-0 overflow-auto" style="line-height: 1.1rem; max-height: 200px;">
                                            @foreach ($client->allowedAccounts as $account)
                                                <li class="mb-2">
                                                    <div>{{ $account->name }}</div>
                                                    <div><span class="text-gray">{{ $account->email }}</span></div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        @if ($client->is_subscription_active)
                                            <span class="text-xs text-success">{{ $client->subscription_status }}</span>
                                        @else
                                            <span class="text-xs text-danger">{{ $client->subscription_status }}</span>
                                            <span class="badge badge-danger">Vencida</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-dark">{{ $client->cards_usage }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-block btn-xs btn-outline-primary"
                                            href="{{ route('clients.cards.theme', $client) }}">
                                            <i class="nav-icon far fa fa-palette"></i>
                                            Tema
                                        </a>
                                        <a class="btn btn-block btn-xs btn-outline-primary"
                                            href="{{ route('clients.cards.index', $client) }}">
                                            <i class="nav-icon far fa-address-card"></i>
                                            Tarjetas
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-block btn-xs btn-outline-primary"
                                            href="{{ route('clients.fields.scopes', $client) }}">
                                            Rango de Campos
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-block btn-xs btn-outline-primary"
                                            href="{{ route('clients.edit', $client) }}">
                                            Editar Cliente
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    {{ $clients->appends(['search' => $search])->links() }}
                </div>
            </div>

        </div>
    </div>

@endsection
