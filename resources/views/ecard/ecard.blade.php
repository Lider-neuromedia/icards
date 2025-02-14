@extends('layouts.simple')

@php
    $version = env('ASSETS_VERSION', 1);
    $headerGradient = '';
    $headerBackground = '';
    $headerImage = '';
    $headerClass = '';
    $logoClass = $ecard->has_logo_bg === '1' ? 'header-logo-bg' : '';

    if ($theme->header_bg_type == 'header_bg_color') {
        $headerClass = 'header-bg-color';
        $headerBackground = "{$theme->header_bg_color}";
    } elseif ($theme->header_bg_type == 'header_bg_gradient') {
        $headerClass = 'header-bg-gradient';
        $color1 = $theme->header_bg_gradient[0];
        $color2 = $theme->header_bg_gradient[1];
        $direction = $theme->header_bg_gradient[2];

        if ($direction == 'vertical') {
            $headerGradient = "linear-gradient(to bottom, $color1, $color2)";
        } elseif ($direction == 'horizontal') {
            $headerGradient = "linear-gradient(to right, $color1, $color2)";
        } elseif ($direction == 'diagonal') {
            $headerGradient = "linear-gradient(to right bottom, $color1, $color2)";
        } elseif ($direction == 'circular') {
            $headerGradient = "radial-gradient(circle, $color1, $color2)";
        }
    } elseif ($theme->header_bg_type == 'header_bg_image') {
        $headerClass = 'header-bg-image';
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
@endphp

{{-- TODO: traer de .env --}}
@section('title', "{$ecard->name} | {$ecard->company} | iCard")

@if ($ecard->description)
    @section('meta-description', $ecard->description)
@endif

@section('meta')
    <meta name="analytics-url" content="{{ $card->url }}">
    <meta name="analytics-card-id" content="{{ $card->id }}">
@endsection

@section('content')

    <header class="header {{ $headerClass }}">
        <div class="wrapper">

            @if ($ecard->logo)
                <div class="header-logo {{ $logoClass }}">
                    <img
                        width="30px"
                        height="auto"
                        alt="{{ __('Company Logo') }}"
                        src="{{ url("storage/cards/$ecard->logo") }}?v={{ $version }}"
                    >
                </div>
            @endif

            @if ($ecard->profile)
                @php
                    $profileImage = url("storage/cards/$ecard->profile");
                    $profileImage = "url('{$profileImage}')";
                @endphp
                <div class="header-profile" style="--profile-image: {{ $profileImage }};"></div>
            @endif

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
                                @if ($ac_key == 'phone')
                                    <a
                                        href="tel:{{ $value }}"
                                        class="track-event"
                                        data-event="contact-by-call"
                                    >
                                        <i class="icofont-phone"></i>
                                        <span>{{ __('Call') }}</span>
                                    </a>
                                @endif

                                @if ($ac_key == 'email')
                                    <a
                                        href="mailto:{{ $value }}"
                                        class="track-event"
                                        data-event="contact-by-email"
                                    >
                                        <i class="icofont-email"></i>
                                        <span>{{ __('Email') }}</span>
                                    </a>
                                @endif

                                @if ($ac_key == 'whatsapp')
                                    <a
                                        target="_blank"
                                        class="track-event"
                                        data-event="contact-by-whatsapp"
                                        href="https://api.whatsapp.com/send?phone={{ $value }}&text={!! $ecard->whatsapp_message !!}"
                                    >
                                        <i class="icofont-brand-whatsapp"></i>
                                        <span>{{ __('Text Me') }}</span>
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
                <div class="content-description">{!! $ecard->description !!}</div>
            @endif

            <nav class="content-contact-list">
                <div class="content-social-actions">
                    <a
                        class="action-black-button track-event"
                        data-event="save-contact"
                        href="{{ $card->vcard }}"
                    >
                        {{ __('Add Contact') }}
                    </a>
                </div>
                <ul>
                    @foreach ($ecard->contact_list as $cl)
                        @foreach ($cl as $cl_key => $value)
                            @if ($value)
                                @php
                                    $is_linkable = true;
                                    $link = "$value";
                                    $track_event = '';
                                    $break_word_class = '';

                                    if ($cl_key == 'phone1' || $cl_key == 'phone2' || $cl_key == 'cellphone') {
                                        $link = "tel:$value";
                                        $track_event = 'contact-by-call';
                                    } elseif ($cl_key == 'email') {
                                        $link = "mailto:$value";
                                        $track_event = 'contact-by-email';
                                        $break_word_class = 'break-word';
                                    } elseif ($cl_key == 'web') {
                                        $track_event = 'visit-web';
                                        $break_word_class = 'break-word';
                                    }

                                    if (in_array($cl_key, ['address'])) {
                                        $is_linkable = false;
                                    }
                                @endphp

                                <li>
                                    @if ($is_linkable)
                                        <a
                                            class="track-event {{ $break_word_class }}"
                                            data-event="{{ $track_event }}"
                                            href="{{ $link }}"
                                            @if ($cl_key == 'web') target="_blank" @endif
                                        >
                                            <img
                                                width="30px"
                                                height="30px"
                                                src="{{ mix("assets/contact-$cl_key.png") }}"
                                                alt="{{ __('Contact data icon') }}"
                                            >
                                            {!! $value !!}
                                        </a>
                                    @else
                                        <span class="{{ $break_word_class }}">
                                            <img
                                                width="30px"
                                                height="30px"
                                                src="{{ mix("assets/contact-$cl_key.png") }}"
                                                alt="{{ __('Contact data icon') }}"
                                            >
                                            {!! $value !!}
                                        </span>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    @endforeach
                </ul>
            </nav>

            <div class="content-divisor"></div>

            <nav class="content-social-list">
                <h2 class="content-social-title">{{ __('Social Media') }}</h2>
                <ul>
                    @foreach ($ecard->social_list as $sl)
                        @foreach ($sl as $sl_key => $sl_value)
                            @if ($sl_value)
                                <li>
                                    <a
                                        href="{{ $sl_value }}"
                                        target="_blank"
                                        class="track-event"
                                        data-event="visit-{{ $sl_key }}"
                                    >
                                        <img
                                            width="30px"
                                            height="30px"
                                            src="{{ mix("assets/social-$sl_key.png") }}"
                                            alt="{{ __('Social media icon') }}"
                                        >
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endforeach
                </ul>
                <div class="content-social-actions">
                    <a
                        class="action-black-button track-event"
                        data-event="save-contact"
                        href="{{ $card->vcard }}"
                    >
                        {{ __('Add Contact') }}
                    </a>
                </div>
                <div class="content-social-actions">
                    <a
                        class="action-black-button track-event"
                        data-event="share-contact"
                        target="_blank"
                        href="https://api.whatsapp.com/send?text={{ $card->vcard }}"
                    >
                        {{ __('Share') }}
                    </a>
                </div>
            </nav>

            <div class="content-divisor"></div>

            <article class="content-ecard">
                <canvas
                    id="canvas-card"
                    width="320"
                    height="440"
                ></canvas>
                <div class="content-ecard-download-container">
                    <button
                        class="content-ecard-download track-event"
                        data-event="save-image"
                        id="donwload-canvas-button"
                        type="button"
                    >
                        {{ __('Download Image') }}
                    </button>
                </div>
            </article>

        </div>
    </main>

    <footer class="footer">
        <div class="wrapper">
            <p>{!! __('Neuromedia :year &copy; All rights reserved', ['year' => date('Y')]) !!}</p>
        </div>
    </footer>

    <!-- Imagen necesaria para generar img desde el canvas -->
    <img
        id="image1"
        src=""
        alt="Canvas"
        style="display: none;"
    >

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
            canDrawCompany: false,
            imageLogo: "{{ $imageLogo }}",
            imageQR: "{{ url("storage/cards/{$card->qr_code}") }}?v={{ $version }}",
            mainColor: "<?= $theme->main_color ?>",
            name: "{{ $ecard->name }}",
            cargo: "{{ $ecard->cargo }}",
            company: "{{ $ecard->company }}",
        }
    </script>

@endsection
