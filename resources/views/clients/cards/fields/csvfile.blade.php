@php
    $field_key = 'csv_file';
    $field = [
        'label' => 'Archivo CSV',
        'help' => 'tamaño maximo 10mb',
    ];
@endphp

<div class="mb-3 {{ $field_key }}_wp" id="{{ $field_key }}_wp">
    <label class="form-label" for="{{ $field_key }}">
        {{ $field['label'] }}
        @if (isset($field['help']))
            <small class="text-muted font-italic">
                {{ $field['help'] }}
            </small>
        @endif
    </label>

    <div class="form-group">
        <div class="custom-file @error($field_key) is-invalid @enderror">
            <input
                name="{{ $field_key }}"
                id="{{ $field_key }}"
                type="file"
                class="custom-file-input file-field"
                lang="es"
                accept="text/csv, .csv"
            >
            <label class="custom-file-label" for="{{ $field_key }}">
                Seleccionar archivo
            </label>
        </div>

        @error($field_key)
            <span class="invalid-feedback" role="alert">
                {{ $message }}
            </span>
        @enderror
    </div>
</div>
