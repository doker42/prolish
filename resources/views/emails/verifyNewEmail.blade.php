@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/verify_account_email.png')}}">


    <div style="margin-top: 6.6%;margin-bottom: 6.6%;padding-left: 12.3%;padding-right: 12.3%;">{{trans('custom.new_email_validation.you_launched_changing_email', ['new_email' => $verify_new_mail->email], $lang)}}</div>
    <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a style="
    display: block;
    color: black;
    text-transform: uppercase;
    background: #FCC537;
    width: fit-content;
    padding: 2% 8%;
    text-decoration:none;
    border-radius: 100px;
    margin: auto;
    font-size: 16px;
" href="{{route('verify_new_email', ['token' => $verify_new_mail->token])}}">{{trans('custom.new_email_validation.verify_email_change', [], $lang)}}</a></div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $lang)!!}</div>


@endsection
