<div class="form-group @error('seller_id') has-error has-feedback @enderror">
    <label for="seller_id">Vendedor</label>

    @php
        $selected_seller = old('seller_id') ?: null;

        if ($client->id) {
            $current_seller = $client->sellers()->first();

            if ($selected_seller == null && $current_seller != null) {
                $selected_seller = $current_seller->id;
            }
        }
    @endphp

    <select class="form-control @error('seller_id') is-invalid @enderror" id="seller_id" name="seller_id" required>
        <option value="">Seleccione un Vendedor</option>

        @foreach ($sellers as $seller)
            <option
                @if($seller->id == $selected_seller) selected @endif
                value="{{$seller->id}}">
                {{$seller->name}}
            </option>
        @endforeach
    </select>

    @error('seller_id')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>