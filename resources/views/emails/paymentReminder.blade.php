@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;"
         src="{{ asset('images/email/payment_reminder_email.png')}}">

    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.letter_dear_user', ['user_name' => $opts['user_name']], $opts['lang'])}},</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.this_is_the_reminder_that_the_validity_period_of_your_tariff_plan', ['tarif_name' => $opts['tarif_name'], 'summ' => $opts['summ']], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.if_you_have_automatic_renewal', ['date' => $opts['date']], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.if_you_dont_please_renew', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.if_you_want_to_change_or_cancel_your', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>


@endsection