@php
    $back_route = route('cards.index');

    if (isUserAdmin()) {
        $back_route = route('clients.cards.index', $client);
    }
@endphp

<div class="row">
    @foreach ($groups as $group_key => $group)
        @if (hasGroupWithSpecificFields($client, $group_key))
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-primary">{{ $group['label'] }}</div>
                    <div class="card-body">

                        @foreach ($group['values'] as $field)
                            @php
                                $isFieldSpecific = isFieldSpecific($client, $group_key, $field['key']);
                            @endphp

                            @if ($isFieldSpecific)
                                @php
                                    $field_key = $group_key . '_' . $field['key'];
                                    $field_type_name = $field['type'];
                                @endphp

                                @include('clients.cards.fields.' . $field_type_name)
                            @endif
                        @endforeach

                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

<div class="row">
    <div class="col-md-12 my-5">
        {{-- TODO: __() --}}
        <a class="btn btn-dark" href="{{ $back_route }}">
            Volver
        </a>
        <button class="btn btn-primary" type="submit">
            Guardar
        </button>
    </div>
</div>
