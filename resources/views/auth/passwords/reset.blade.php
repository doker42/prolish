@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 mt-5">
            <div class="card">

                <div class="card-body pl-5 pr-5">
                    <div class="h3 font-weight-bold text-center mb-3 mt-2">{{ __('custom.reset_password') }}</div>
                    <form method="POST" action="{{ route('password.request') }}" aria-label="{{ __('Reset Password') }}" class="ml-5 mr-5">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <label for="email" class="col-form-label text-md-right pb-0 font-weight-bold">{{ __('custom.email') }}</label>

                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif

                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-form-label text-md-right pb-0 font-weight-bold">{{ __('custom.password') }}</label>


                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif

                        </div>

                        <div class="form-group row mb-4">
                            <label for="password-confirm" class="col-form-label text-md-right pb-0 font-weight-bold">{{ __('custom.confirm_password') }}</label>


                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                        </div>

                        <div class="form-group row">
                            <div class="w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('custom.reset_password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
