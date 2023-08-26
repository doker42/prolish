@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/deleted_company_email.png')}}">

    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.deleted_c', [], $opts['lang'])}}</div>

    @if(!empty($opts['account_deleted']) && $opts['account_deleted'] == 1)

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_has_been_deleted_with_projects', ['company_name' => $opts['company_name']], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.you_still_have_access_to_your_projects', [], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.to_check_your_projects_click_below', [], $opts['lang'])}}</div>

        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="{{ config('app.url')}}" style="
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
">{{trans('custom.check_my_projects',[], $opts['lang'])}}</a></div>

    @else

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_has_been_deleted_with_projects', ['company_name' => $opts['company_name']], $opts['lang'])}} {{trans('custom.as_you_participated_only_in_projects', ['company_name' => $opts['company_name']], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.you_can_register_a_new_account', [], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.in_other_cases_ignore_this_mail', [], $opts['lang'])}}</div>

    @endif

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>

@endsection