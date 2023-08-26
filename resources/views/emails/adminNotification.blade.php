@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;"
         src="{{ asset('images/email/new_message_email.png')}}">

    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;">{{ trans('custom.new_message', [], $opts['lang'])}}</div>

    <div style="font-size: 16px;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    text-align: center;
    padding-right: 12.3%;">{{trans('custom.from', [], $opts['lang'])}} <span style="font-size:24px;"> {{$opts['admin_name']}}  {{trans('custom.ceo_my3dcloud')}}</span></div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{$opts['message']}}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!! trans('custom.in_case_you_need_contact_administrator_press_here', ['here' => '<a href=mailto:"'.$opts['admin_email'].'">'.trans('custom.here',[],$opts['lang']).'</a>' ], $opts['lang']) !!}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    text-decoration: none;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>



@endsection