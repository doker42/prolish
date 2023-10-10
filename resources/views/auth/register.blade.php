@extends('layouts.app')

@section('cust_scripts')


    <script src="{{asset('js/jquery-3.5.1.min.js')}}" defer></script>
    <script src="{{asset('js/jquery-ui.min.js')}}" defer></script>
    <script src="{{asset('js/register.js')}}" defer></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" defer integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

@endsection

@section('cust_styles')
    <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
    <style>
        .card-body {
            min-height: 622px !important;
            height: 100%;
        }
        .notify-company{
            font-size: 80%;
            color: orange;
            display: none;
        }
        .lds-wrapper {
            width: 100%;
            height: 512px;
            display: flex;
            justify-content: center;
            align-items: center;
            display: none;
        }
.lds-roller {
  margin: auto;
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.lds-roller div {
  animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  transform-origin: 40px 40px;
}
.lds-roller div:after {
  content: " ";
  display: block;
  position: absolute;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #298494;
  margin: -4px 0 0 -4px;
}
.lds-roller div:nth-child(1) {
  animation-delay: -0.036s;
}
.lds-roller div:nth-child(1):after {
  top: 63px;
  left: 63px;
}
.lds-roller div:nth-child(2) {
  animation-delay: -0.072s;
}
.lds-roller div:nth-child(2):after {
  top: 68px;
  left: 56px;
}
.lds-roller div:nth-child(3) {
  animation-delay: -0.108s;
}
.lds-roller div:nth-child(3):after {
  top: 71px;
  left: 48px;
}
.lds-roller div:nth-child(4) {
  animation-delay: -0.144s;
}
.lds-roller div:nth-child(4):after {
  top: 72px;
  left: 40px;
}
.lds-roller div:nth-child(5) {
  animation-delay: -0.18s;
}
.lds-roller div:nth-child(5):after {
  top: 71px;
  left: 32px;
}
.lds-roller div:nth-child(6) {
  animation-delay: -0.216s;
}
.lds-roller div:nth-child(6):after {
  top: 68px;
  left: 24px;
}
.lds-roller div:nth-child(7) {
  animation-delay: -0.252s;
}
.lds-roller div:nth-child(7):after {
  top: 63px;
  left: 17px;
}
.lds-roller div:nth-child(8) {
  animation-delay: -0.288s;
}
.lds-roller div:nth-child(8):after {
  top: 56px;
  left: 12px;
}
@keyframes lds-roller {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

    </style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 mt-4">
            <div class="card">

                <div class="card-body pl-5 pr-5">
                    <div class="h3 font-weight-bold text-center mb-3 mt-2">{{ __('custom.register') }}</div>
                    <div class="lds-wrapper">
                      <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                    </div>
                    <form method="POST" action="{{ route('register') }}" aria-label="{{ __('custom.register')  }}" class="ml-5 mr-5">
                        @csrf

                        <div class="form-group row mb-1">
                            <label for="name" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.user_name') }}</label>


                                <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif

                        </div>

                        <div class="form-group row mb-1">
                            <label for="email" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.email') }}</label>


                                <input data-invites_link="{{url('companies_invited')}}"  id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>
                                <input id="input_company_id"  type="text"  class="d-none">
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif

                        </div>


                        <div class="form-group row mb-1">
                            <label for="company" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.company') }}</label>


                                <input id="company" data-link="{{url('companies_autocompleate')}}" type="text" class="form-control{{ $errors->has('company') ? ' is-invalid' : '' }}" name="company" value="{{ old('company') }}" required>
                                <input id="company_id"  type="text"  name="company_id" class="d-none">
                                <span role="warning" class="notify-company"><strong>{{trans('custom.company_join_validation.registration_after_admin_verification')}}</strong></span>
                                @if ($errors->has('company'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('company') }}</strong>
                                    </span>
                                @endif

                        </div>

                        <div class="form-group row mb-1">
                            <label for="job_title" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.job_title') }}</label>


                            <input id="job_title" type="text"
                                   class="form-control{{ $errors->has('job_title') ? ' is-invalid' : '' }}"
                                   name="job_title" value="{{ old('job_title') }}" required>

                            @if ($errors->has('job_title'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('job_title') }}</strong>
                                    </span>
                            @endif

                        </div>

                        <div class="form-group row mb-1">
                            <label for="password"
                                   class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.password') }}</label>


                            <input id="password" type="password"
                                   class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                   name="password" required>

                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif

                        </div>

                        <div class="form-group row mb-4">
                            <label for="password-confirm" class="col-form-label text-md-left pb-0 font-weight-bold">{{ __('custom.confirm_password') }}</label>


                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                        </div>

                        <div class="form-group row mb-2">
                            <div class="w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('custom.register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade styled_modal" id="alertModal" role="dialog" ref="alertModal">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center">{{trans('custom.company_join_validation.you_still_have_access_to_invited_company')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Ok</button>
            </div>
        </div>

    </div>
</div>
</div>

@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const ldsRoller = document.querySelector('.lds-wrapper');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      form.style.display = 'none';
      ldsRoller.style.display = 'flex';

      fetch(form.action, {
        method: form.method,
        body: new FormData(form),
      })
        .then(function (response) {
            window.location.href = '/login';
        })
        .catch(function (error) {
          ldsRoller.style.display = 'none';
          console.error('Network error:', error);
        });
    });
  });
</script>
