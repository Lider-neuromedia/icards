<div class="form-group">
    <label class="form-label" for="cards">*Cantidad de tarjetas</label>
    <input
        required
        class="form-control @error('cards') is-invalid @enderror"
        type="number"
        min="1"
        step="1"
        name="cards"
        id="cards"
        value="{{ old('cards') ?: $subscription->cards }}" >

    @error('cards')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>