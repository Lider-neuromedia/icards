@php
    $key = 'card_number_' . $card->id;
    $keyForm = 'card_number_' . $card->id . '_form';
@endphp

<form method="post" id="{{ $keyForm }}" action="{{ $formUrl }}">
    @csrf

    <div class="form-group @error('slug_number') has-error has-feedback @enderror">
        @php
            $selected_value = old('slug_number') ?: $card->slug_number;
        @endphp

        <select class="form-control @error('slug_number') is-invalid @enderror" name="slug_number">
            @foreach ($card->card_numbers as $cardNumber)
                <option @if ($cardNumber == $selected_value) selected @endif value="{{ $cardNumber }}">
                    {{ $cardNumber }}
                </option>
            @endforeach
        </select>

        @error('slug_number')
            <span class="invalid-feedback" role="alert">
                {{ $message }}
            </span>
        @enderror
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector("#{{ $keyForm }} select")
            .addEventListener('change', function(e) {
                document.getElementById("{{ $keyForm }}").submit();
            });
    });
</script>
