<div class="form-group">
    <label class="form-label" for="name">*Nombre</label>
    <input
        required
        class="form-control @error('name') is-invalid @enderror"
        type="text"
        name="name"
        id="name"
        value="{{ old('name') ?: $client->name }}" >

    @error('name')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>