<div class="form-group">
    <label class="form-label" for="{{$field_key}}">
        {{$field['label']}}
        @if (isset($field['help']))
            <small class="text-muted">{{$field['help']}}</small>
        @endif
    </label>
    <input
        type="color"
        class="form-control @error($field_key) is-invalid @enderror"
        name="{{$field_key}}"
        id="{{$field_key}}"
        value="{{ old($field_key) ?: $card->field($group_key, $field['key'])}}">

    @error($field_key)
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>