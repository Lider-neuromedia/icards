<div class="form-check font-weight-bold mb-2 {{ $field_key }}_wp" id="{{ $field_key }}_wp">
    @php
        $checked = (old($field_key) ?: $card->field($group_key, $field['key'])) == '1';
    @endphp

    <input
        class="form-check-input"
        type="checkbox"
        name="{{ $field_key }}"
        id="{{ $field_key }}"
        value="1"
        @if ($checked) checked @endif
    >

    <label class="form-check-label @error($field_key) is-invalid @enderror" for="{{ $field_key }}">
        {{ $field['label'] }}
        @if (isset($field['help']))
            <small class="d-block text-muted font-italic">{{ $field['help'] }}</small>
        @endif
    </label>

    @error($field_key)
        <span class="invalid-feedback" role="alert">
            {{ $message }}
        </span>
    @enderror
</div>
