<div class="form-group {{$field_key}}_wp" id="{{$field_key}}_wp">
    <label class="form-label" for="{{$field_key}}">
        {{$field['label']}}
        @if (isset($field['help']))
            <small class="text-muted font-italic">{{$field['help']}}</small>
        @endif
    </label>

    @php
        $old_field_key_0 = "$field_key.0";
        $old_field_key_1 = "$field_key.1";
        $old_field_key_2 = "$field_key.2";

        $field_key_0 = $field_key . "[0]";
        $field_key_1 = $field_key . "[1]";
        $field_key_2 = $field_key . "[2]";

        $currentValue = $card->field($group_key, $field['key']);
        $color0 = old($old_field_key_0) ?: $currentValue[0];
        $color1 = old($old_field_key_1) ?: $currentValue[1];
        $direction = old($old_field_key_2) ?: $currentValue[2];
    @endphp

    <div class="row">
        <div class="col-6 col-md-4 mb-1">

            {{-- Primer Color --}}
            <input
                type="color"
                class="form-control @error($old_field_key_0) is-invalid @enderror"
                name="{{$field_key_0}}"
                id="{{$field_key_0}}"
                value="{{$color0}}">

            @error($old_field_key_0)
                <span class="invalid-feedback" role="alert">
                    {{$message}}
                </span>
            @enderror

        </div>
        <div class="col-6 col-md-4 mb-1">

            {{-- Segundo Color --}}
            <input
                type="color"
                class="form-control @error($old_field_key_1) is-invalid @enderror"
                name="{{$field_key_1}}"
                id="{{$field_key_1}}"
                value="{{$color1}}">

            @error($old_field_key_1)
                <span class="invalid-feedback" role="alert">
                    {{$message}}
                </span>
            @enderror

        </div>
        <div class="col-12 col-md-4 mb-1">

            <select class="form-control @error($old_field_key_2) is-invalid @enderror" name="{{$field_key_2}}" id="{{$field_key_2}}">
                <option @if($direction == "vertical") selected @endif value="vertical">Orientación Vertical</option>
                <option @if($direction == "horizontal") selected @endif value="horizontal">Orientación Horizontal</option>
                <option @if($direction == "diagonal") selected @endif value="diagonal">Orientación Diagonal</option>
                <option @if($direction == "circular") selected @endif value="circular">Orientación Circular</option>
            </select>

            @error($old_field_key_2)
                <span class="invalid-feedback" role="alert">
                    {{$message}}
                </span>
            @enderror

        </div>
    </div>
</div>