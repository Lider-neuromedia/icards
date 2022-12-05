@extends('layouts.simple')

@php
    $version = env('ASSETS_VERSION', 1);
    $headerGradient = "";
    $headerBackground = "";
    $headerImage = "";
    $headerClass = "";
    $logoClass = $ecard->has_logo_bg === "1" ? "header-logo-bg" : "";

    if ($theme->header_bg_type == "header_bg_color") {
        $headerClass = "header-bg-color";
        $headerBackground = "{$theme->header_bg_color}";
    } else if ($theme->header_bg_type == "header_bg_gradient") {
        $headerClass = "header-bg-gradient";
        $color1 = $theme->header_bg_gradient[0];
        $color2 = $theme->header_bg_gradient[1];
        $direction = $theme->header_bg_gradient[2];

        if ($direction == "vertical") {
            $headerGradient = "linear-gradient(to bottom, $color1, $color2)";
        } else if ($direction == "horizontal") {
            $headerGradient = "linear-gradient(to right, $color1, $color2)";
        } else if ($direction == "diagonal") {
            $headerGradient = "linear-gradient(to right bottom, $color1, $color2)";
        } else if ($direction == "circular") {
            $headerGradient = "radial-gradient(circle, $color1, $color2)";
        }
    } else if ($theme->header_bg_type == "header_bg_image") {
        $headerClass = "header-bg-image";
        $headerImage = url("storage/cards/{$theme->header_bg_image}");
        $headerImage = "$headerImage?v=$version";
        $headerImage = "url($headerImage)";
    }

    $themeStyles = "<style>
            body {
                --bg-light-color: #ffffff;
                --bg-dark-color: #1d1e22;
                --white-color: #ffffff;
                --main-color: {$theme->main_color};
                --header-bg-color: $headerBackground;
                --header-bg-gradient: $headerGradient;
                --header-bg-image: $headerImage;
                --header-text-color: {$theme->header_text_color};
                --logo-bg: {$ecard->logo_bg};
            }
        </style>";

    // Profile Image.
    $profileImage = null;
    if ($ecard->profile) {
        $profileImage = url("storage/cards/$ecard->profile");
        $profileImage = "url('{$profileImage}')";
    }

    function getActionContactsValue($actions, $key)
    {
        foreach ($actions as $ac) {
            foreach ($ac as $ac_key => $value) {
                if ($value) {
                    if ($ac_key == $key) {
                        return $value;
                    }
                }
            }
        }
        return null;
    }

    function getContactListValue($contactList, $key)
    {
        foreach ($contactList as $cl) {
            foreach ($cl as $cl_key => $value) {
                if ($cl_key == $key) {
                    return $value;
                }
            }
        }
        return null;
    }

    const ICONS = [
        'cellphone' => "smart-phone",
        'phone1' => "phone",
        'phone2' => "phone",
        'email' => "email",
        'web' => "globe",
        'address' => "location-pin",
        'facebook' => "facebook",
        'instagram' => "instagram",
        'linkedin' => "linkedin",
        'twitter' => "twitter",
        'youtube' => "youtube-play",
    ];
@endphp

@section('title', "{$ecard->name} | {$ecard->company} | iCard")

@if ($ecard->description)
    @section('meta-description', $ecard->description)
@endif

@section('meta')
    <meta name="analytics-url" content="{{ $card->url }}">
    <meta name="analytics-card-id" content="{{ $card->id }}">
@endsection

@section('content')

    <div class="header-bg {{$headerClass}}"></div>

    <main class="header">
        <div class="wrapper">

            {{-- Logo Empresa --}}
            @if ($ecard->logo)
                <div class="header-logo {{$logoClass}}">
                    <img width="100px" height="auto" alt="Logo Empresa"
                        src="{{ url("storage/cards/$ecard->logo") }}?v={{$version}}">
                </div>
            @endif

            {{-- Logo Usuario --}}
            @if ($profileImage)
                <div class="header-profile" style="--profile-image: {{ $profileImage }};"></div>
            @endif

            {{-- Nombre --}}
            <h1 class="header-description header-name">
                <strong>{{ $ecard->name }}</strong>
            </h1>

            {{-- Cargo --}}
            <div class="header-description header-cargo">
                {{ $ecard->cargo }}
            </div>

            {{-- Descripción --}}
            @if ($ecard->description)
                <div class="content-description">
                    {!! $ecard->description !!}
                </div>
            @endif

            {{-- Redes --}}
            <nav class="content-social-list">
                <ul>
                    @foreach ($ecard->social_list as $sl)
                        @foreach ($sl as $sl_key => $sl_value)

                            @if ($sl_value)
                                <li>
                                    <a
                                        href="{{ $sl_value }}"
                                        target="_blank"
                                        class="track-event"
                                        data-event="visit-{{$sl_key}}">
                                        <i class="icofont-{{ICONS[$sl_key]}}"></i>
                                    </a>
                                </li>
                            @endif

                        @endforeach
                    @endforeach
                </ul>
            </nav>

            {{-- Empresa --}}
            {{-- <div class="header-description header-company">
                {{ $ecard->company }}
            </div> --}}

            {{-- Datos --}}
            <nav class="content-contact-list">
                <ul>
                    @foreach ($ecard->contact_list as $cl)
                        @foreach ($cl as $cl_key => $value)

                            @if ($value)

                                @php
                                    $is_linkable = true;
                                    $link = "$value";
                                    $track_event = "";
                                    $break_word_class = "";

                                    if ($cl_key == 'phone1' || $cl_key == 'phone2' || $cl_key == 'cellphone') {
                                        $link = "tel:$value";
                                        $track_event = "contact-by-call";
                                    } else if ($cl_key == 'email') {
                                        $link = "mailto:$value";
                                        $track_event = "contact-by-email";
                                        $break_word_class = "break-word";
                                    } else if ($cl_key == 'web') {
                                        $track_event = "visit-web";
                                        $break_word_class = "break-word";
                                    }

                                    if (in_array($cl_key, ['address'])) {
                                        $is_linkable = false;
                                    }
                                @endphp

                                <li class="contact-list-item">
                                    @if ($is_linkable)

                                        <a
                                            class="list-item-el track-event {{$break_word_class}}"
                                            data-event="{{$track_event}}"
                                            href="{{ $link }}"
                                            @if ($cl_key == 'web') target="_blank" @endif>
                                            <span class="icon">
                                                <i class="icofont-{{ICONS[$cl_key]}}"></i>
                                            </span>
                                            {!! $value !!}
                                        </a>

                                    @else

                                        <span class="list-item-el {{$break_word_class}}">
                                            <span class="icon">
                                                <i class="icofont-{{ICONS[$cl_key]}}"></i>
                                            </span>
                                            {!! $value !!}
                                        </span>

                                    @endif
                                </li>
                            @endif

                        @endforeach
                    @endforeach
                </ul>
            </nav>

            {{-- Actions --}}
            {{-- <section class="header-actions">
                @php
                    $actionPhone = getActionContactsValue($ecard->action_contacts, 'phone');
                    $actionEmail = getActionContactsValue($ecard->action_contacts, 'email');
                    $actionWhatsapp = getActionContactsValue($ecard->action_contacts, 'whatsapp');
                    $actionWeb = getContactListValue($ecard->contact_list, 'web');
                @endphp

                <div class="wrapper">
                    <!-- Action Email -->
                    @if ($actionEmail)
                        <a href="mailto:{{ $actionEmail }}" class="header-action track-event" data-event="contact-by-email">
                            <i class="icofont-email"></i>
                            <span>Envianos un correo</span>
                        </a>
                    @endif

                    <!-- Action Web -->
                    @if ($actionWeb)
                        <a target="_blank" class="header-action track-event break-word" data-event="visit-web" href="{{ $actionWeb }}">
                            <i class="icofont-globe"></i>
                            <span>Visita nuestra Web</span>
                        </a>
                    @endif

                    <!-- Action QR -->
                    <button type="button" class="header-action track-event" data-event="save-contact" href="{{ $card->vcard }}">
                        <i class="icofont-qr-code"></i>
                        Muestra tu código QR!
                    </button>

                    <!-- Action Guardar -->
                    <a class="header-action track-event" data-event="save-contact" href="{{ $card->vcard }}">
                        <i class="icofont-user-alt-2"></i>
                        Guardar Contacto
                    </a>

                    <!-- Action Whatsapp -->
                    @if ($actionWhatsapp)
                        <a target="_blank" class="header-action track-event" data-event="contact-by-whatsapp"
                            href="https://api.whatsapp.com/send?phone={{ $actionWhatsapp }}&text={!! $ecard->whatsapp_message !!}">
                            <i class="no-rotate icofont-brand-whatsapp"></i>
                            <span>Enviar Whatsapp</span>
                        </a>
                    @endif

                    <!-- Action Phone -->
                    @if ($actionPhone)
                        <a href="tel:{{ $actionPhone }}" class="header-action track-event" data-event="contact-by-call">
                            <i class="icofont-phone"></i>
                            <span>Llamar</span>
                        </a>
                    @endif
                </div>
            </section> --}}

            {{-- Tarjeta --}}
            <section class="header-card">
                <a
                    class="action-black-button track-event"
                    data-event="share-contact"
                    target="_blank"
                    href="https://api.whatsapp.com/send?text={{ $card->vcard }}">
                    Compartir
                </a>

                <a
                    class="action-black-button track-event"
                    data-event="save-contact"
                    href="{{ $card->vcard }}">
                    Guardar Contacto
                </a>

                <article class="content-ecard">
                    <canvas id="canvas-card" width="320" height="440"></canvas>
                </article>

                <button
                    class="action-black-button content-ecard-download track-event"
                    data-event="save-image"
                    id="donwload-canvas-button"
                    type="button">
                    Descargar Imagen
                </button>
            </section>

        </div>
    </main>

    <footer class="footer">
        <div class="wrapper">
            <p>
                <a target="_blank" href="https://www.neuromedia.com.co/">Neuromedia</a>
                {{date('Y')}} &copy; Todos los derechos reservados
            </p>
        </div>
    </footer>

    <!-- Imagen necesaria para generar img desde el canvas -->
    <img id="image1" src="" alt="Canvas" style="display: none;">

@endsection

@section('styles')

    <link href="{{ mix($templateFiles->stylesPath) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('icofont/icofont.min.css') }}">

    {!! $themeStyles !!}

    <script src="{{ mix('js/app.js') }}" defer></script>

    @php
        $headerLogo = $ecard->logo ? url("storage/cards/{$ecard->logo}") . "?v=$version" : null;
        $cardLogo = $ecard->logo_card ? url("storage/cards/{$ecard->logo_card}") . "?v=$version" : null;
        $imageLogo = $cardLogo ? $cardLogo : $headerLogo;
    @endphp

    <script>
        window.card = {
            canDrawLogo: true,
            canDrawCompany: true,
            imageLogo: "{{$imageLogo}}",
            imageQR: "{{ url("storage/cards/{$card->qr_code}") }}?v={{$version}}",
            mainColor: "<?= $theme->main_color ?>",
            name: "{{ $ecard->name }}",
            cargo: "{{ $ecard->cargo }}",
            company: "{{ $ecard->company }}",
        }
    </script>

@endsection
