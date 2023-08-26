@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/forgot_password_email.png')}}">


    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{trans('custom.forgot_your_password', [], $lang)}}</div>
    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.if_you_want_to_reset_password',[], $lang)}}<br>{{trans('custom.this_link_will_lead_password_form',[], $lang)}}</div>

    <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                href="{{url(config('app.url').route('password.reset', $token, false))}}" style="
    display: block;
    color: black;
    text-transform: uppercase;
    background: #FCC537;
    width: fit-content;
    padding: 2% 8%;
    border-radius: 100px;
    text-decoration:none;
    margin: auto;
    font-size: 16px;
">{{trans('custom.password_reset',[], $lang)}}</a></div>


    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $lang)!!}<br><br>{{trans('custom.didnt_ask_for_change_password',[], $lang)}}</div>





@endsection