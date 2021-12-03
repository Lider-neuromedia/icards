@extends('layouts.simple')

@section('content')

    <header class="header">
        <div class="wrapper">
            <div class="header-logo">
                <img width="30px" height="auto" src="{{ url("storage/cards/$ecard->logo") }}?v={{ env('ASSETS_VERSION', 1) }}">
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

                        @if ($value && !in_array($ac_key, ['whatsapp_message']))

                            <div class="header-action">
                                @if ($ac_key == "phone")
                                    <a href="tel:{{$value}}">
                                        <i class="icofont-phone"></i>
                                        <span>Llamar</span>
                                    </a>
                                @endif

                                @if ($ac_key == "email")
                                    <a href="mailto:{{$value}}">
                                        <i class="icofont-email"></i>
                                        <span>Enviar Correo</span>
                                    </a>
                                @endif

                                @if ($ac_key == "whatsapp")
                                    <a href="https://api.whatsapp.com/send?phone={{$value}}&text={!!$ecard->whatsapp_message!!}">
                                        <i class="icofont-brand-whatsapp"></i>
                                        <span>Enviar Whatsapp</span>
                                    </a>
                                @endif
                            </div>

                        @endif

                    @endforeach
                @endforeach
            </nav>
        </div>
    </header>

    <main class="content">
        <div class="wrapper">

            @if ($ecard->description)
                <div class="content-description">{{ $ecard->description }}</div>
            @endif

            <nav class="content-contact-list">
                <div class="content-social-actions">
                    <a class="action-black-button" href="{{$card->vcard}}">Guardar Contacto</a>
                </div>
                <ul>
                    @foreach ($ecard->contact_list as $cl)
                        @foreach ($cl as $cl_key => $value)

                            @if ($value)

                                @php
                                    $link = "$value";
                                    if ($cl_key == "phone1" || $cl_key == "phone2" || $cl_key == "cellphone") {
                                        $link = "tel:$value";
                                    } else if ($cl_key == "email") {
                                        $link = "mailto:$value";
                                    }
                                @endphp

                                <li>
                                    <a href="{{$link}}" @if ($cl_key == "web") target="_blank" @endif>
                                        <img width="30px" height="30px" src="{{ mix("assets/contact-$cl_key.png") }}">
                                        {{ $value }}
                                    </a>
                                </li>

                            @endif

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

                            @if ($sl_value)

                                <li>
                                    <a href="{{ $sl_value }}" target="_blank">
                                        <img width="30px" height="30px" src="{{ mix("assets/social-$sl_key.png") }}">
                                    </a>
                                </li>

                            @endif

                        @endforeach
                    @endforeach
                </ul>
                <div class="content-social-actions">
                    <a class="action-black-button" href="{{$card->vcard}}">Guardar Contacto</a>
                </div>
                <div class="content-social-actions">
                    <a class="action-black-button" href="https://api.whatsapp.com/send?text={{$card->vcard}}">
                        Compartir
                    </a>
                </div>
            </nav>

            <div class="content-divisor"></div>

            <article class="content-ecard">
                <canvas id="canvas-card" width="320" height="440"></canvas>
                <div class="content-ecard-download-container">
                    <button class="content-ecard-download" id="donwload-canvas-button" type="button">Descargar Imagen</button>
                </div>

                {{-- <div class="ecard">
                    <div class="ecard-border">
                        <img class="ecard-logo" width="30px" height="30px" src="{{ url("storage/cards/{$ecard->logo}") }}?v={{ env('ASSETS_VERSION', 1) }}">
                        <div class="ecard-name">
                            {{ $ecard->name }}<br>
                            {{ $ecard->cargo }}
                        </div>
                        <img class="ecard-qr" src="{{ url("storage/cards/{$card->qr_code}") }}?v={{ env('ASSETS_VERSION', 1) }}" alt="eCard">
                        <div class="ecard-action">Escanear Código QR</div>
                    </div>
                </div> --}}
            </article>

        </div>
    </main>

    <footer class="footer">
        <div class="wrapper">
            <p>De acuerdo a nuestra política de protección de datos no compartimos información real de nuestros clientes</p>
            <p>Esta es una página de referencia para que visualice el funcionamiento de una eCard, nuestros créditos, logotipos o páginas web nunca serán visualizados en las eCards de nuestros clientes</p>
        </div>
    </footer>

    <!-- Imagen necesaria para generar img desde el canvas -->
    <img id="image1" src="" alt="" style="display: none;">

@endsection

@section('styles')

    <link rel="stylesheet" href="{{mix('icofont/icofont.min.css')}}">
    <style>

        body {
            --bg-light-color: #ffffff;
            --bg-dark-color: #1d1e22;
            --white-color: #ffffff;
            --main-color: <?= $theme->main_color ?>;
            --header-bg-color: <?= $theme->header_bg_color ?>;
            --header-text-color: <?= $theme->header_text_color ?>;
        }

    </style>

    <script src="{{ mix('js/app.js') }}" defer></script>

    <script>

        window.card = {
            imageLogo: "{{url("storage/cards/{$ecard->logo}")}}?v={{env('ASSETS_VERSION', 1)}}",
            imageQR: "{{url("storage/cards/{$card->qr_code}")}}?v={{env('ASSETS_VERSION', 1)}}",
            mainColor: "<?= $theme->main_color ?>",
            name: "{{$ecard->name}}",
            cargo: "{{$ecard->cargo}}",
        }

    </script>

@endsection
