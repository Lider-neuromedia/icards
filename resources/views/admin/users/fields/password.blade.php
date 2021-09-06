@php
    $is_required = $user->id ? false : true;
@endphp

@if (!$is_required)
    <div class="alert alert-warning mt-5">
        Solo llene los campos de contraseña si piensa cambiarla.
    </div>
@endif

<div class="form-group">
    <label class="form-label" for="password">
        @if ($is_required) * @endif
        Contraseña
    </label>

    <input
        class="form-control @error('password') is-invalid @enderror"
        type="password"
        name="password"
        id="password"
        minlength="8"
        @if ($is_required) required @endif>

    @error('password')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="password_confirmation">
        @if ($is_required) * @endif
        Repetir Contraseña
    </label>

    <input
        class="form-control @error('password_confirmation') is-invalid @enderror"
        type="password"
        name="password_confirmation"
        id="password_confirmation"
        @if ($is_required) required @endif>

    @error('password_confirmation')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>