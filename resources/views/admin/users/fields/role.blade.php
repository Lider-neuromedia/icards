<div class="form-group @error('role') has-error has-feedback @enderror">
    <label for="role">Rol</label>

    @php
        $selected_role = old('role') ?: $user->role;
    @endphp

    <select class="form-control @error('role') is-invalid @enderror" id="role" name="role">
        @foreach ($roles as $role_id => $role_name)
            <option
                @if($role_id == $selected_role) selected @endif
                value="{{$role_id}}">
                {{$role_name}}
            </option>
        @endforeach
    </select>

    @error('role')
        <span class="invalid-feedback" role="alert">
            {{$message}}
        </span>
    @enderror
</div>