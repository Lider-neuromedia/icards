@extends('layouts.simple')

@section('content')

    <header class="header">
        <div class="wrapper">
            <div class="header-logo">
                <img width="30px" height="30px" src="{{ url("storage/cards/$ecard->logo") }}?v={{ env('ASSETS_VERSION', 1) }}">
            </div>

            <h1 class="header-description header-name">
                <strong>{{ $ecard->name }}</strong>
            </h1>
            <span class="header-divisor"></span>
            <div class="header-description header-cargo">{{ $ecard->cargo }}</div>
            <div class="header-description header-company"><strong>{{ $ecard->company }}</strong></div>
            <span class="header-divisor"></span>

            <nav class="header-actions">
                @foreach ($ecard->action_contacts as $ac)
                    @foreach ($ac as $ac_key => $value)

                        <div class="header-action">
                            @if ($ac_key == "phone")
                                <a href="tel:{{$value}}">
                                    <img width="30px" height="30px" src="{{ url("assets/action-$ac_key.png") }}?v={{ env('ASSETS_VERSION', 1) }}">
                                    <span>Llamar</span>
                                </a>
                            @endif

                            @if ($ac_key == "email")
                                <a href="mailto:{{$value}}">
                                    <img width="30px" height="30px" src="{{ url("assets/action-$ac_key.png") }}?v={{ env('ASSETS_VERSION', 1) }}">
                                    <span>Enviar Correo</span>
                                </a>
                            @endif

                            @if ($ac_key == "whatsapp")
                                <a href="https://api.whatsapp.com/send?phone={{$value}}&text=Hola,%20quiero%20comprar%20eCards%20para%20mi%20negocio">
                                    <img width="30px" height="30px" src="{{ url("assets/action-$ac_key.png") }}?v={{ env('ASSETS_VERSION', 1) }}">
                                    <span>Enviar Whatsapp</span>
                                </a>
                            @endif
                        </div>

                    @endforeach
                @endforeach
            </nav>
        </div>
    </header>

    <main class="content">
        <div class="wrapper">
            <div class="content-description">{{ $ecard->description }}</div>

            <nav class="content-contact-list">
                <ul>
                    @foreach ($ecard->contact_list as $cl)
                        @foreach ($cl as $cl_key => $value)

                            @php
                                $link = "$value";
                                if ($cl_key == "phone" || $cl_key == "cellphone") {
                                    $link = "tel:$value";
                                } else if ($cl_key == "email") {
                                    $link = "mailto:$value";
                                }
                            @endphp

                            <li>
                                <a href="{{$link}}" @if ($cl_key == "web") target="_blank" @endif>
                                    <img width="30px" height="30px" src="{{ url("assets/contact-$cl_key.png") }}">
                                    {{ $value }}
                                </a>
                            </li>

                        @endforeach
                    @endforeach
                </ul>
            </nav>

            <div class="content-divisor"></div>

            <nav class="content-social-list">
                <h2 class="content-social-title">Redes Sociales</h2>
                <ul>
                    @foreach ($ecard->social_list as $sl)
                        @foreach ($sl as $sl_key => $sl_value)

                            <li>
                                <a href="{{ $sl_value }}" target="_blank">
                                    <img width="30px" height="30px" src="{{ url("assets/social-$sl_key.png") }}?v={{ env('ASSETS_VERSION', 1) }}">
                                </a>
                            </li>

                        @endforeach
                    @endforeach
                </ul>
                <div class="content-social-actions">
                    <a href="{{ url("storage/cards/{$card->slug}.vcf") }}">Descargar eCard</a>
                </div>
            </nav>

            <div class="content-divisor"></div>

            <article class="content-ecard">
                <div class="ecard">
                    <div class="ecard-border">
                        <img class="ecard-logo" width="30px" height="30px" src="{{ url("storage/cards/{$ecard->logo}") }}?v={{ env('ASSETS_VERSION', 1) }}">
                        <div class="ecard-name">
                            {{ $ecard->name }}<br>
                            {{ $ecard->cargo }}
                        </div>
                        <img class="ecard-qr" src="{{ url("storage/cards/{$card->qr_code}") }}?v={{ env('ASSETS_VERSION', 1) }}" alt="eCard">
                        <div class="ecard-action">Escanear Código QR</div>
                    </div>
                </div>
            </article>
        </div>
    </main>

    <footer class="footer">
        <div class="wrapper">
            <p>De acuerdo a nuestra política de protección de datos no compartimos información real de nuestros clientes</p>
            <p>Esta es una página de referencia para que visualice el funcionamiento de una eCard, nuestros créditos, logotipos o páginas web nunca serán visualizados en las eCards de nuestros clientes</p>
        </div>
    </footer>

@endsection

@section('styles')

    <style>

        body {
            --bg-light-color: #ffffff;
            --bg-dark-color: #1d1e22;
            --white-color: #ffffff;
            --main-color: {!! $theme->main_color !!};
        }

    </style>

@endsection
