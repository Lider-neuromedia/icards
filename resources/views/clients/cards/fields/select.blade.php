<div class="form-group {{$field_key}}_wp" id="{{$field_key}}_wp">
    <label class="form-label" for="{{$field_key}}">
        {{$field['label']}}
        @if (isset($field['help']))
            <small class="text-muted font-italic">{{$field['help']}}</small>
        @endif
    </label>

    @php
        $value = old($field_key) ?: $card->field($group_key, $field['key']);
    @endphp

    <select
        class="form-control @error($field_key) is-invalid @enderror"
        name="{{$field_key}}"
        id="{{$field_key}}">

        @foreach ($field['options'] as $option)
            <option
                @if($value == $option['id']) selected @endif
                value="{{$option['id']}}">
                {{$option["name"]}}
            </option>
        @endforeach
    </select>

    @error($field_key)
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>