@php
    $index_route = route('cards.index');
    $create_route = route('cards.create');
    $create_multiple_route = route('cards.create-multiple');
    $theme_route = route('cards.theme');
    
    if (
        auth()
            ->user()
            ->isAdmin()
    ) {
        $index_route = route('clients.cards.index', $client);
        $create_route = route('clients.cards.create', $client);
        $create_multiple_route = route('clients.cards.create-multiple', $client);
        $theme_route = route('clients.cards.theme', $client);
    }
@endphp

@extends('layouts.dashboard')

@section('title', 'Tarjetas')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Tarjetas</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="alert alert-light text-center border-primary bg-white">
                Límite de tarjetas ({{ $client->cards_usage }})
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tarjetas</h3>

                    <div class="card-tools">
                        <form action="{{ $index_route }}" method="get">
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

                                @if (!$cards->isEmpty() && $cards->first()->use_card_number)
                                    <th># Tarjeta</th>
                                @endif

                                <th class="text-center">Total Visitas</th>
                                <th class="text-center">QR Visitas</th>
                                <th class="text-right">
                                    <a href="{{ $theme_route }}" class="btn btn-success btn-sm" title="Tema General">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        Tema General
                                    </a>

                                    @if (!$client->isCardsLimitReached())
                                        <a href="{{ $create_multiple_route }}" class="btn btn-primary btn-sm"
                                            title="Crear Multiples Tarjetas">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            Crear Varias
                                        </a>
                                        <a href="{{ $create_route }}" class="btn btn-primary btn-sm" title="Crear Tarjeta">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            Crear
                                        </a>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            @if ($cards->count() == 0)
                                <tr>
                                    <td class="text-center" colspan="3">No hay tarjetas</td>
                                </tr>
                            @endif

                            @foreach ($cards as $card)
                                @php
                                    $edit_route = route('cards.edit', $card);
                                    $destroy_route = route('cards.destroy', $card);
                                    $isAdmin = auth()
                                        ->user()
                                        ->isAdmin();
                                    
                                    if ($isAdmin) {
                                        $edit_route = route('clients.cards.edit', [$client, $card]);
                                        $destroy_route = route('clients.cards.destroy', [$client, $card]);
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div>{{ $card->field('others', 'name') }}</div>
                                        @if ($card->use_card_number)
                                            <a target="_blank" href="{{ $card->url_number }}">{{ $card->url_number }}</a>
                                        @else
                                            <a target="_blank" href="{{ $card->url }}">{{ $card->url }}</a>
                                        @endif
                                    </td>

                                    @if ($card->use_card_number)
                                        <td class="text-center">
                                            @include('clients.cards.fields.card-number', [
                                                'client' => $client,
                                                'card' => $card,
                                            ])
                                        </td>
                                    @endif

                                    <td class="text-center">{{ $card->visits }}</td>
                                    <td class="text-center">{{ $card->qr_visits }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-sm btn-success" href="{{ $edit_route }}"
                                            title="Editar Tarjeta">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                            Editar
                                        </a>

                                        @include('partials.delete', [
                                            'id_form' => 'delete-card-form-' . $card->id,
                                            'label' => 'Borrar Tarjeta',
                                            'route' => $destroy_route,
                                            'tiny' => true,
                                        ])
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    {{ $cards->appends(['search' => $search])->links() }}
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Estadísticas</h3>
                    <div class="card-tools">
                        <a href="{{ route('analytics.download', $client->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-download" aria-hidden="true"></i>
                            <span>Descargar</span>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table">

                        <thead>
                            <th>Nombre</th>
                            @foreach ($events as $event)
                                <th class="text-center" title="{{ $event->description }}">
                                    {{ $event->title }}
                                </th>
                            @endforeach
                        </thead>

                        <tbody>
                            @if ($cards->count() == 0)
                                <tr>
                                    <td class="text-center" colspan="3">No hay tarjetas</td>
                                </tr>
                            @endif

                            @foreach ($cards as $card)
                                <tr>
                                    <td>
                                        <a target="_blank" href="{{ $card->url }}">
                                            {{ $card->field('others', 'name') }}
                                        </a>
                                    </td>
                                    @foreach ($events as $event)
                                        @php
                                            $action = $card
                                                ->statistics()
                                                ->where('action', $event->key)
                                                ->first();
                                        @endphp

                                        <td class="text-center">
                                            {{ $action == null ? 0 : $action->data }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
