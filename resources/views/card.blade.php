@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Card</div>
                    <div class="card-body">
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <img width="30px" height="30px" src="{{ url("storage/$card/$ecard->logo") }}">
                        </li>

                        <li class="list-group-item">Nombre: {{ $ecard->name }}</li>
                        <li class="list-group-item">Cargo: {{ $ecard->cargo }}</li>
                        <li class="list-group-item">Descripción: {{ $ecard->description }}</li>

                        <li class="list-group-item">Acciones de Contacto</li>

                        @foreach ($ecard->action_contacts as $ac)
                            @foreach ($ac as $ac_key => $value)

                                @php
                                    $link = "$value";

                                    if ($ac_key == "phone" || $ac_key == "cellphone") {
                                        $link = "tel:$value";
                                    } else if ($ac_key == "email") {
                                        $link = "mailto:$value";
                                    }
                                @endphp

                                <li class="list-group-item">
                                    <img width="30px" height="30px" src="{{ url("assets/action-$ac_key.png") }}">
                                    <a href="{{$link}}" @if ($ac_key == "web") target="_blank" @endif>{{ $value }}</a>
                                </li>

                            @endforeach
                        @endforeach

                        <li class="list-group-item">Contacto</li>

                        @foreach ($ecard->contact_list as $cl)
                            @foreach ($cl as $cl_key => $value)

                                @php
                                    $link = "$value";

                                    if ($cl_key == "phone" || $ac_key == "cellphone") {
                                        $link = "tel:$value";
                                    } else if ($cl_key == "email") {
                                        $link = "mailto:$value";
                                    }
                                @endphp

                                <li class="list-group-item">
                                    <img width="30px" height="30px" src="{{ url("assets/contact-$cl_key.png") }}">
                                    <a href="{{$link}}" @if ($cl_key == "web") target="_blank" @endif>{{ $value }}</a>
                                </li>

                            @endforeach
                        @endforeach

                        <li class="list-group-item">Redes Sociales</li>

                        @foreach ($ecard->social_list as $sl)

                            @foreach ($sl as $sl_key => $sl_value)

                                <li class="list-group-item">
                                    <img width="30px" height="30px" src="{{ url("assets/social-$sl_key.png") }}">
                                    <a href="{{ $sl_value }}" target="_blank">{{ $sl_value }}</a>
                                </li>

                            @endforeach

                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
