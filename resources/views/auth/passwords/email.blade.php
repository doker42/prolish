@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 mt-5">
            <div class="card">

                <div class="card-body pl-5 pr-5">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                        <div class="h3 font-weight-bold text-center mb-3 mt-2">{{ __('custom.reset_password') }}</div>
                    <form method="POST" action="{{ route('password.email') }}" aria-label="{{ __('custom.reset_password') }}" class="ml-5 mr-5">
                        @csrf

                        <div class="form-group row mb-4">
                            <label for="email" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.email') }}</label>


                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif

                        </div>

                        <div class="form-group row">
                            <div class="w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('custom.send_reset_url') }}
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
