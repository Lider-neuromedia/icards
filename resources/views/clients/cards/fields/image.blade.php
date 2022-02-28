<div class="mb-3">
    <label class="form-label" for="{{ $field_key }}">
        {{ $field['label'] }}
        @if (isset($field['help']))
            <small class="text-muted font-italic">{{$field['help']}}</small>
        @endif
    </label>

    <div class="form-group">
        <div class="custom-file @error($field_key) is-invalid @enderror">
            <input name="{{ $field_key }}" id="{{ $field_key }}" type="file" class="custom-file-input file-field"
                lang="es" accept="image/png, image/jpeg">
            <label class="custom-file-label" for="{{ $field_key }}">
                Seleccionar imagen
            </label>
        </div>

        @error($field_key)
            <span class="invalid-feedback" role="alert">
                {{ $message }}
            </span>
        @enderror
    </div>

    @php
        $url = $card->field($group_key, $field['key']);
        $currentWrapper = "{$field_key}_current_wp";
    @endphp

    @if ($url != '')
        <div class="d-flex align-items-end g-1" id="{{ $currentWrapper }}">
            <input type="hidden" name="{{ $field_key }}_current"
                value="{{ $card->field($group_key, $field['key']) }}">

            <img src="{{ url('storage/cards/' . $card->field($group_key, $field['key'])) }}" class="img-thumbnail"
                width="200px" height="auto" alt="imagen">

            <button type="button" class="btn btn-sm btn-dark ml-1"
                onclick="document.getElementById('{{ $currentWrapper }}').remove();">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    @endif
</div>
