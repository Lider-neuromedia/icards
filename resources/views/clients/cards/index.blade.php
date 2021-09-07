@extends('layouts.dashboard')

@section('title', 'Tarjetas')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Tarjetas</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="alert alert-light text-center border-primary bg-white">
                Límite de tarjetas ({{auth()->user()->cards_usage}})
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>

                    <div class="card-tools">
                        <form action="{{route('cards.index')}}" method="get">
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
                                <th>URL</th>
                                <th>Nombre</th>
                                <th class="text-right">
                                    @if (!auth()->user()->isCardsLimitReached())
                                        <a href="{{route('cards.create')}}" class="btn btn-primary btn-xs" title="Crear Tarjeta">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($cards as $card)
                                <tr>
                                    <td><a target="_blank" href="{{$card->url}}">{{$card->url}}</a></td>
                                    <td>{{$card->field('others', 'name')}}</td>
                                    <td class="text-right">
                                        <a class="btn btn-xs btn-success" href="{{route('cards.edit', $card->id)}}" title="Editar Tarjeta">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    {{$cards->appends(['search' => $search])->links()}}
                </div>
            </div>

        </div>
    </div>

@endsection
