@extends('layouts.dashboard')

@section('title', $client->name . ' / Configuración de Campos')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('clients.index') }}">
            {{-- TODO: __() --}}
            Clientes
        </a>
    </li>
    <li class="breadcrumb-item active">
        Configuración de Campos
    </li>
@endsection

@section('content')
    <div class="container-fluid">

        <form action="{{ route('clients.fields.scopes', $client) }}" method="post">
            @csrf

            <div class="row">
                <div class="col-md-12 my-2 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <a class="btn btn-dark" href="{{ route('clients.index') }}">
                            Volver
                        </a>
                        <button class="btn btn-primary" type="submit">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">

                @foreach ($fields as $groupKey => $group)
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title text-primary font-weight-bold">
                                    {{ $group['label'] }}
                                </h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th class="text-center">
                                                Marcar como campo general.<br>
                                                (Se edita una sola vez para todas las tarjetas)
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($group['values'] as $i => $value)
                                            @php
                                                $valueKey = $value['key'];
                                                $fullKey = "$groupKey.$valueKey";
                                            @endphp

                                            <tr>
                                                <td class="text-nowrap">
                                                    <label
                                                        class="font-weight-normal"
                                                        role="button"
                                                        for="scopes[{{ $fullKey }}][general]"
                                                    >
                                                        {{ $value['label'] }}
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <input
                                                        type="hidden"
                                                        name="scopes[{{ $fullKey }}][key]"
                                                        value="{{ $fullKey }}"
                                                    />
                                                    <input
                                                        @if ($scopes[$fullKey]) checked @endif
                                                        role="button"
                                                        type="checkbox"
                                                        id="scopes[{{ $fullKey }}][general]"
                                                        name="scopes[{{ $fullKey }}][general]"
                                                        value="1"
                                                    />
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>

        <form action="{{ route('clients.fields.scopes-reset', $client) }}" method="post">
            @csrf
            @method('PATCH')

            <div class="my-4 d-flex justify-content-end border-top py-2">
                <button class="btn btn-danger" type="submit">
                    Restablecer Valores por Defecto
                </button>
            </div>
        </form>

    </div>
@endsection
