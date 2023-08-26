@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;"
         src="{{ asset('images/email/trial_period_email.png')}}">

    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.trial_period', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.you_have_30_day_period_platform_workspace', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.we_will_still_send_the_reminder', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.in_case_your_account_wont_be', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>


@endsection