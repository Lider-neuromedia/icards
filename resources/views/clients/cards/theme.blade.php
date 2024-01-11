@php
    $store_route = route('cards.theme-store');

    if (isUserAdmin()) {
        $store_route = route('clients.cards.theme-store', $client);
    }
@endphp

@extends('layouts.dashboard')

@section('title', 'Tema Visual')

@section('breadcrumbs')
    @if (isUserAdmin())
        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clientes</a></li>
    @endif
    <li class="breadcrumb-item active">Tema Visual</li>
@endsection

@section('pre-scripts')
    <script>
        window.groups = @json($groups);
    </script>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-sm-8">

                @if (!$card)
                    <div class="alert alert-warning">
                        Primero debe crear una tarjeta antes de ajustar el tema.
                    </div>
                @else
                    {{-- Formulario de editar --}}

                    <form action="{{ $store_route }}" method="post" enctype="multipart/form-data">
                        @csrf

                        @if (isUserClient() && $filters->account)
                            <input type="hidden" name="account" value="{{ $filters->account }}" />
                        @endif

                        <div class="row">
                            @foreach ($groups as $group_key => $group)
                                @if (hasGroupWithGeneralFields($client, $group_key))
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header text-primary">{{ $group['label'] }}</div>
                                            <div class="card-body">

                                                @foreach ($group['values'] as $field)
                                                    @php
                                                        $isFieldGeneral = isFieldGeneral($client, $group_key, $field['key']);
                                                    @endphp

                                                    @if ($isFieldGeneral)
                                                        @php
                                                            $field_key = $group_key . '_' . $field['key'];
                                                        @endphp

                                                        @if ($field['type'] == 'text')
                                                            @include('clients.cards.fields.text')
                                                        @endif

                                                        @if ($field['type'] == 'textarea')
                                                            @include('clients.cards.fields.textarea')
                                                        @endif

                                                        @if ($field['type'] == 'image')
                                                            @include('clients.cards.fields.image')
                                                        @endif

                                                        @if ($field['type'] == 'color')
                                                            @include('clients.cards.fields.color')
                                                        @endif

                                                        @if ($field['type'] == 'boolean')
                                                            @include('clients.cards.fields.boolean')
                                                        @endif

                                                        @if ($field['type'] == 'gradient')
                                                            @include('clients.cards.fields.gradient')
                                                        @endif

                                                        @if ($field['type'] == 'select')
                                                            @include('clients.cards.fields.select')
                                                        @endif
                                                    @endif
                                                @endforeach

                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-12 my-5">
                                <button class="btn btn-primary" type="submit">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

            </div>
        </div>

    </div>
@endsection
