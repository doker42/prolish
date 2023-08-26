@extends('emails.emailTemplate')


@section('email_content')

    @if($opts['type'] == 'request')

        <img style="margin:auto;display:block;margin-top: 9%;"
             src="{{ asset('images/email/verify_request_email.png')}}">

        <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.company_verification.verification_request')}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_verification.user_sent_verification_request', ['user_name' => $opts['user_name'], 'company_name' => $opts['company_title']], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_verification.you_can_review_company_info', [], $opts['lang'])}}</div>
        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="{{ config('app.url').'/#/companies/'.$opts['company_id'].'/edit'}}" style="
    display: block;
    color: black;
    text-transform: uppercase;
    background: #FCC537;
    width: fit-content;
    text-decoration:none;
    padding: 2% 8%;
    border-radius: 100px;
    margin: auto;
    font-size: 16px;
">{{trans('custom.company_verification.review_the_company',[], $opts['lang'])}}</a></div>

    @endif

    @if($opts['type'] == 'cancelled')
        <img style="margin:auto;display:block;margin-top: 9%;"
             src="{{ asset('images/email/company_verification_cancelled_email.png')}}">

        <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.company_verification.company_unverified', [],$opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.company_verification.verification_of_your_company_been_cancelled', ['company_name' => $opts['company_title'], 'date' => $opts['registered_data'], 'admin_email'=> '<a href="mailto:'.env('ADMINISTRATOR_EMAIL').'">'.env('ADMINISTRATOR_EMAIL').'</a>'], $opts['lang'])!!}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>

    @endif

    @if($opts['type'] == 'approved')
        <img style="margin:auto;display:block;margin-top: 9%;"
             src="{{ asset('images/email/company_verification_approved_email.png')}}">

        <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.company_verification.verified_successfully', [],$opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.company_verification.your_company_has_been_verified', ['company_name' => $opts['company_title']], $opts['lang'])!!}</div>

        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="{{ config('app.url')}}" style="
    display: block;
    color: black;
    text-transform: uppercase;
    background: #FCC537;
    width: fit-content;
    text-decoration:none;
    padding: 2% 8%;
    border-radius: 100px;
    margin: auto;
    font-size: 16px;
">{{trans('custom.company_verification.begin_your_work',[], $opts['lang'])}}</a></div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>

    @endif

@endsection