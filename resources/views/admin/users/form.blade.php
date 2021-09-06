@include('admin.users.fields.name')
@include('admin.users.fields.email')
{{-- @include('admin.users.fields.role') --}}
@include('admin.users.fields.password')

<div class="row">
    <div class="col-md-12 my-5">
        <a class="btn btn-dark" href="{{ route('users.index') }}">Volver</a>
        <button class="btn btn-primary" type="submit">
            Guardar
        </button>
    </div>
</div>