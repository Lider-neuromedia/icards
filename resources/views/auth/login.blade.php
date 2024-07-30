@extends('layouts.empty')

@section('body-class', 'page-login')

@section('content')

    <img
        class="login-bg"
        src="{{ mix('assets/logo-big.svg') }}"
        alt="NeuroMedia"
    >

    <div class="login-container row justify-content-center">
        <div class="col-sm-10 col-md-6 col-lg-4">
            <div class="card bg-transparent shadow-none border-0">
                <div class="card-header border-0 text-center mb-4">
                    <img
                        class="login-logo"
                        src="{{ mix('assets/logo.svg') }}"
                        width="90%"
                        height="auto"
                        alt="Neuromedia"
                    >
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row border-bottom border-white align-items-center">
                            <label for="email" class="col-auto col-form-label">
                                <img
                                    src="{{ mix('assets/user-icon.svg') }}"
                                    width="auto"
                                    height="26px"
                                    alt="User"
                                >
                            </label>

                            <div class="col p-0">
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    class="form-control @error('email') is-invalid @enderror bg-transparent text-white border-0"
                                    value="{{ old('email') }}"
                                    placeholder="{{ __('E-Mail Address') }}"
                                    required
                                    autofocus
                                >

                                @error('email')
                                    <span class="invalid-feedback text-white" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row border-bottom border-white align-items-center">
                            <label for="password" class="col-auto col-form-label">
                                <img
                                    src="{{ mix('assets/password-icon.svg') }}"
                                    width="auto"
                                    height="26px"
                                    alt="Password"
                                >
                            </label>

                            <div class="col p-0">
                                <input
                                    id="password"
                                    type="password"
                                    autocomplete="current-password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror bg-transparent text-white border-0"
                                    placeholder="{{ __('Password') }}"
                                    required
                                >

                                @error('password')
                                    <span class="invalid-feedback text-white" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row form-remember-field">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <div class="toggle-check"></div>
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remember"
                                        id="remember"
                                        {{ old('remember') ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label text-white" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12 text-center">
                                <button type="submit"
                                    class="btn btn-block btn-light text-purple rounded-pill font-weight-bold mt-5 mb-4"
                                >
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link text-white" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
