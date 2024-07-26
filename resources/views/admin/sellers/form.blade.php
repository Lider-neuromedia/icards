@include('admin.sellers.fields.name')

<div class="row">
    <div class="col-md-12 my-5">
        <a class="btn btn-dark" href="{{ route('sellers.index') }}">
            {{-- TODO: __() --}}
            Volver
        </a>
        <button class="btn btn-primary" type="submit">
            Guardar
        </button>
    </div>
</div>
