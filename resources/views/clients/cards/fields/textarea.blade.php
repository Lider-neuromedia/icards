<div class="form-group {{$field_key}}_wp" id="{{$field_key}}_wp">
    <label class="form-label" for="{{$field_key}}">
        {{$field['label']}}
        @if (isset($field['help']))
            <small class="text-muted font-italic">{{$field['help']}}</small>
        @endif
    </label>
    <textarea
        class="form-control @error($field_key) is-invalid @enderror"
        maxlength="10000"
        name="{{$field_key}}"
        id="{{$field_key}}"
        cols="30"
        rows="5">{{ old($field_key) ?: $card->field($group_key, $field['key'])}}</textarea>

    @error($field_key)
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>