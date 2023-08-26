@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 mt-5">
            <div class="card">

                <div class="card-body pl-5 pr-5">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif
                        <div class="h3 font-weight-bold text-center mb-3 mt-2">{{ __('custom.login') }}</div>
                    <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}" class="ml-5 mr-5">
                        @csrf

                        <div class="form-group row mb-1">
                            <label for="email" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.email') }}</label>


                            <input id="email" type="email"
                                   class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                                   value="{{ old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                            @endif

                        </div>

                        <div class="form-group row">
                            <label for="password"
                                   class="col-form-label text-md-right pb-0 font-weight-bold">{{ __('custom.password') }}</label>


                            <input id="password" type="password"
                                   class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                   name="password" required>

                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif

                        </div>

                        <div class="form-group row">
                            <div class="w-100">
                                <div class="checkbox">
                                    <input class="custom-checkbox" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="mb-0" for="remember">
                                        {{ __('custom.remember_me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-1">
                            <div class="w-100 text-center">
                                <button type="submit" class="btn btn-primary d-block w-100 mb-3">
                                    {{ __('custom.login') }}
                                </button>

                                <a class="btn btn-outline-primary d-block" href="{{ route('register') }}">
                                    {{ __('custom.register') }}
                                </a>
                            </div>

                            <a class="col-sm-12 mt-3 mb-3 btn btn-link text-sm-center" href="{{ route('password.request') }}">
                                {{ __('custom.forgot_password') }}
                            </a>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
