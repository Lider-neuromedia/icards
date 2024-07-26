@include('admin.clients.fields.seller')
@include('admin.clients.fields.name')
@include('admin.clients.fields.email')

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            {{-- TODO: __() --}}
            Suscripción
        </h3>
    </div>
    <div class="card-body">
        @include('admin.clients.fields.dates')
        @include('admin.clients.fields.cards')
    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Cuentas Habilitadas
        </h3>
    </div>
    <div class="card-body">
        @include('admin.clients.fields.allowed-accounts')
    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Contraseña
        </h3>
    </div>
    <div class="card-body">
        @include('admin.clients.fields.password')
    </div>
</div>

<div class="row">
    <div class="col-md-12 my-5">
        <a class="btn btn-dark" href="{{ route('clients.index') }}">
            Volver
        </a>
        <button class="btn btn-primary" type="submit">
            Guardar
        </button>
    </div>
</div>
