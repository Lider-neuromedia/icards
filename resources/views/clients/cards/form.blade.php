<div class="row">
    @foreach ($groups as $group_key => $group)
        @if (\App\CardField::hasGroupWithSpecificFields($group_key))

            <div class="col-12">
                <div class="card">
                    <div class="card-header text-primary">{{$group['label']}}</div>
                    <div class="card-body">

                        @foreach ($group['values'] as $field)
                            @if ($field['general'] == false)

                                @php
                                    $field_key = $group_key . '_' . $field['key'];
                                @endphp

                                @if ($field['type'] == 'text')
                                    @include('clients.cards.fields.text')
                                @endif

                                @if ($field['type'] == 'textarea')
                                    @include('clients.cards.fields.textarea')
                                @endif

                                @if ($field['type'] == 'image')
                                    @include('clients.cards.fields.image')
                                @endif

                                @if ($field['type'] == 'color')
                                    @include('clients.cards.fields.color')
                                @endif

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
        <a class="btn btn-dark" href="{{ route('cards.index') }}">Volver</a>
        <button class="btn btn-primary" type="submit">
            Guardar
        </button>
    </div>
</div>