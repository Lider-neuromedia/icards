<div class="mb-3">
    <label class="form-label" for="{{$field_key}}">{{$field['label']}}</label>
    <div class="form-group">
        <div class="custom-file @error($field_key) is-invalid @enderror">
            <input
                name="{{$field_key}}"
                id="{{$field_key}}"
                type="file"
                class="custom-file-input file-field"
                lang="es"
                accept="image/png, image/jpeg">
            <label class="custom-file-label" for="{{$field_key}}">
                Seleccionar imagen
            </label>
        </div>

        @error($field_key)
            <span class="invalid-feedback" role="alert">
                {{$message}}
            </span>
        @enderror
    </div>

    @php
        $url = $card->field($group_key, $field['key']);
    @endphp

    @if ($url != '')
        <img src="{{url("storage/cards/" . $card->field($group_key, $field['key']))}}"
            class="img-thumbnail"
            width="200px"
            height="auto"
            alt="imagen">
    @endif
</div>