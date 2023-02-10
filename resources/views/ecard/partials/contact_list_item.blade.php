@php
    $is_linkable = true;
    $link = "$value";
    $track_event = '';
    $break_word_class = '';
    
    if (in_array($cl_key, ['phone1', 'phone2', 'cellphone'])) {
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

<li class="contact-list-item">
    @if ($is_linkable)
        <a class="list-item-el track-event {{ $break_word_class }}" data-event="{{ $track_event }}"
            href="{{ $link }}" @if ($cl_key == 'web') target="_blank" @endif>
            <span class="icon">
                <i class="icofont-{{ ICONS[$cl_key] }}"></i>
            </span>
            {!! $value !!}
        </a>
    @else
        <span class="list-item-el {{ $break_word_class }}">
            <span class="icon">
                <i class="icofont-{{ ICONS[$cl_key] }}"></i>
            </span>
            {!! $value !!}
        </span>
    @endif
</li>
