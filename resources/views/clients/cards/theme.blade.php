@extends('layouts.dashboard')

@section('title', 'Tema Visual')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Tema Visual</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-sm-8">

                {{-- Formulario de editar --}}

                <form action="{{ route('cards.theme-store') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        @foreach ($groups as $group_key => $group)
                            @if (\App\CardField::hasGroupWithGeneralFields($group_key))

                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header text-primary">{{$group['label']}}</div>
                                        <div class="card-body">

                                            @foreach ($group['values'] as $field)
                                                @if ($field['general'] == true)

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

            </div>
        </div>

    </div>
@endsection
