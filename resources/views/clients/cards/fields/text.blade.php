<div class="form-group">
    <label class="form-label" for="{{$field_key}}">{{$field['label']}}</label>
    <input
        type="text"
        maxlength="250"
        class="form-control @error($field_key) is-invalid @enderror"
        name="{{$field_key}}"
        id="{{$field_key}}"
        value="{{ old($field_key) ?: $card->field($group_key, $field['key']) }}">

    @error($field_key)
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>