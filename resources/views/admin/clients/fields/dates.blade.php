<div class="form-group">
    <label class="form-label" for="start_at">*Fecha y hora de inicio</label>
    <input
        required
        class="form-control @error('start_at') is-invalid @enderror"
        type="datetime-local"
        name="start_at"
        id="start_at"
        value="{{ old('start_at') ?: $subscription->start_at->format('Y-m-d\TH:i') }}"
    >

    @error('start_at')
        <span class="invalid-feedback" role="alert">
            {{ $message }}
        </span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="finish_at">*Fecha y hora de finalización</label>
    <input
        required
        class="form-control @error('finish_at') is-invalid @enderror"
        type="datetime-local"
        name="finish_at"
        id="finish_at"
        value="{{ old('finish_at') ?: $subscription->finish_at->format('Y-m-d\TH:i') }}"
    >

    @error('finish_at')
        <span class="invalid-feedback" role="alert">
            {{ $message }}
        </span>
    @enderror
</div>
