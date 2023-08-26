@extends('emails.emailTemplate')

@section('email_content')

 <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/verify_account_email.png')}}">


 <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{!! trans('custom.new_email_validation.thank_you_for_choosing_my3d', ['my_3d' => '<br><b><a style="text-decoration:none;color:#0F3238" href="https://my3d.cloud">'.env('APP_NAME').'!</a></b>'], $user->locale) !!}</div>
 <div style="padding-left: 12.3%;padding-right: 12.3%;">{{trans('custom.new_email_validation.in_order_to_complete_confirm_your_email_address',[],$user->locale)}}</div>
 <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"> <a href="{{url('user/verify', [$user->verifyUser->token])}}" style="
    display: block;
    color: black;
    text-transform: uppercase;
    background: #FCC537;
    text-decoration:none;
    width: fit-content;
    padding: 2% 8%;
    border-radius: 100px;
    margin: auto;
    font-size: 16px;
">{{trans('custom.new_email_validation.confirm_email_address',[],$user->locale)}}</a></div>

 <div style="padding-left: 12.3%;padding-right: 12.3%;">{{trans('custom.new_email_validation.well_comunicate_important_updates',[],$user->locale)}}</div>


@endsection