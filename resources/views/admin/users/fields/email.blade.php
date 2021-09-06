<div class="form-group">
    <label class="form-label" for="email">*Correo</label>
    <input
        required
        class="form-control @error('email') is-invalid @enderror"
        type="email"
        name="email"
        id="email"
        value="{{ old('email') ?: $user->email }}" >

    @error('email')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>