@extends('layouts.dashboard')

@section('title', 'Vendedores')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Vendedores</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>

                    <div class="card-tools">
                        <form action="{{ route('sellers.index') }}" method="get">
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
                                <th>Nombre</th>
                                <th class="text-center">Clientes</th>
                                <th class="text-right">
                                    <a href="{{ route('sellers.create') }}" class="btn btn-primary btn-xs"
                                        title="Crear Vendedor">
                                        Crear Vendedor
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($sellers as $seller)
                                <tr>
                                    <td>{{ $seller->name }}</td>
                                    <td class="text-center">{{ $seller->clients()->count() }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-xs btn-primary"
                                            href="{{ route('sellers.edit', $seller) }}">Editar</a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    {{ $sellers->appends(['search' => $search])->links() }}
                </div>
            </div>

        </div>
    </div>

@endsection
