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
        'cellphone' => 'smart-phone',
        'phone1' => 'phone',
        'phone2' => 'phone',
        'email' => 'email',
        'web' => 'globe',
        'address' => 'location-pin',
        'facebook' => 'facebook',
        'instagram' => 'instagram',
        'linkedin' => 'linkedin',
        'twitter' => 'twitter',
        'youtube' => 'youtube-play',
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

    <div class="header-bg {{ $headerClass }}"></div>

    <main class="header">
        <div class="wrapper">

            {{-- Company Logo --}}
            @if ($ecard->logo)
                <div class="header-logo {{ $logoClass }}">
                    <img
                        width="100px"
                        height="auto"
                        alt="{{ __('Company Logo') }}"
                        src="{{ url("storage/cards/$ecard->logo") }}?v={{ $version }}"
                    >
                </div>
            @endif

            {{-- User Logo --}}
            @if ($profileImage)
                <div class="header-profile" style="--profile-image: {{ $profileImage }};"></div>
            @endif

            {{-- Name --}}
            <h1 class="header-description header-name">
                <strong>{{ $ecard->name }}</strong>
            </h1>

            {{-- Role --}}
            <div class="header-description header-cargo">
                {{ $ecard->cargo }}
            </div>

            {{-- Description --}}
            @if ($ecard->description)
                <div class="content-description">
                    {!! $ecard->description !!}
                </div>
            @endif

            {{-- Company --}}
            {{-- <div class="header-description header-company">
                {{ $ecard->company }}
            </div> --}}

            {{-- Actions --}}
            <section class="header-actions">
                @php
                    $actionPhone = getActionContactsValue($ecard->action_contacts, 'phone');
                    $actionEmail = getActionContactsValue($ecard->action_contacts, 'email');
                    $actionWhatsapp = getActionContactsValue($ecard->action_contacts, 'whatsapp');
                    $actionWeb = getContactListValue($ecard->contact_list, 'web');
                @endphp

                <div class="wrapper">
                    <!-- Action Phone -->
                    @if ($actionPhone)
                        <a
                            href="tel:{{ $actionPhone }}"
                            class="header-action track-event"
                            data-event="contact-by-call"
                        >
                            <i class="icofont-phone"></i>
                            <span>{{ __('Call') }}</span>
                        </a>
                    @endif

                    <!-- Action Email -->
                    @if ($actionEmail)
                        <a
                            href="mailto:{{ $actionEmail }}"
                            class="header-action track-event"
                            data-event="contact-by-email"
                        >
                            <i class="icofont-email"></i>
                            <span>{{ __('Email') }}</span>
                        </a>
                    @endif

                    <!-- Action Web -->
                    {{-- @if ($actionWeb)
                        <a target="_blank" class="header-action track-event break-word" data-event="visit-web"
                            href="{{ $actionWeb }}">
                            <i class="icofont-globe"></i>
                            <span>{{ __('Visit our site') }}</span>
                        </a>
                    @endif --}}

                    <!-- Action QR -->
                    {{-- <button type="button" class="header-action track-event" data-event="save-contact" href="{{ $card->vcard }}">
                        <i class="icofont-qr-code"></i>
                        {{ __('Show your QR code!') }}
                    </button> --}}

                    <!-- Action Guardar -->
                    {{-- <a class="header-action track-event" data-event="save-contact" href="{{ $card->vcard }}">
                        <i class="icofont-user-alt-2"></i>
                        {{ __('Add Contact') }}
                    </a> --}}

                    <!-- Action Whatsapp -->
                    @if ($actionWhatsapp)
                        <a
                            target="_blank"
                            class="header-action track-event"
                            data-event="contact-by-whatsapp"
                            href="https://api.whatsapp.com/send?phone={{ $actionWhatsapp }}&text={!! $ecard->whatsapp_message !!}"
                        >
                            <i class="no-rotate icofont-brand-whatsapp"></i>
                            <span>{{ __('Text Me') }}</span>
                        </a>
                    @endif
                </div>
            </section>


            {{-- Datos --}}
            <nav class="content-contact-list">
                <ul style="margin-bottom: 0;">
                    @foreach ($ecard->contact_list as $cl)
                        @foreach ($cl as $cl_key => $value)
                            @if ($value && !in_array($cl_key, ['web']))
                                @include('ecard.partials.contact_list_item', [
                                    'cl_key' => $cl_key,
                                    'value' => $value,
                                ])
                            @endif
                        @endforeach
                    @endforeach
                </ul>
            </nav>

            {{-- {!! cardValue($card, 'contact_list', 'address') !!} --}}
            {{-- {!! cardValue($card, 'contact_list', 'web') !!} --}}

            {{-- Datos: Solo Web --}}
            <nav class="content-contact-list">
                <ul style="margin-top: 0; margin-bottom: 0;">
                    @foreach ($ecard->contact_list as $cl)
                        @foreach ($cl as $cl_key => $value)
                            @if ($value && in_array($cl_key, ['web']))
                                @include('ecard.partials.contact_list_item', [
                                    'cl_key' => $cl_key,
                                    'value' => $value,
                                ])
                            @endif
                        @endforeach
                    @endforeach
                </ul>
            </nav>

            {{-- Social Media --}}
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
                                        data-event="visit-{{ $sl_key }}"
                                    >
                                        <i class="icofont-{{ ICONS[$sl_key] }}"></i>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endforeach
                </ul>
            </nav>

            {{-- Card --}}
            <section class="header-card">
                <a
                    class="action-black-button track-event"
                    data-event="share-contact"
                    target="_blank"
                    href="https://api.whatsapp.com/send?text={{ $card->vcard }}"
                >
                    {{ __('Share') }}
                </a>

                <a
                    class="action-black-button track-event"
                    data-event="save-contact"
                    href="{{ $card->vcard }}"
                >
                    {{ __('Add Contact') }}
                </a>

                <article class="content-ecard">
                    <canvas
                        id="canvas-card"
                        width="320"
                        height="440"
                    ></canvas>
                </article>

                <button
                    class="action-black-button content-ecard-download track-event"
                    data-event="save-image"
                    id="donwload-canvas-button"
                    type="button"
                >
                    {{ __('Download Image') }}
                </button>
            </section>

        </div>
    </main>

    <footer class="footer">
        <div class="wrapper">
            <p>
                <a target="_blank" href="https://www.neuromedia.com.co/">
                    {!! __('Neuromedia :year &copy; All rights reserved', ['year' => date('Y')]) !!}
                </a>
            </p>
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
