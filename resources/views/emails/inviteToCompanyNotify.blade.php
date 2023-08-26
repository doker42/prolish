@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;"
         src="{{ asset('images/email/project_invite_email.png')}}">

    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{ trans('custom.youve_been_invited_company',['company_title' => $opts['company_title']],$opts['lang'])}}</div>


    @if($opts['type'] == 'new_user')

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!! trans('custom.user_have_invited_you_to_the_company_text', ['sender_email' => '<a href="mailto:'.$opts['sender_email'].'">'.$opts['sender_email'].'</a>', 'sender_name' => $opts['sender_name'], 'company_name' => $opts['company_title']], $opts['lang'])!!}</div>


        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.as_user_already_recorded_email_for_company', ['sender_name' => $opts['sender_name'], 'invited_email' => $opts['email']], $opts['lang'])}}</div>
        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.you_will_have_chance_to_change_email', [], $opts['lang'])}}</div>
        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="{{ Config::get('app.url').'/register' }}" style="
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
">{{trans('custom.register_platform',[], $opts['lang'])}}</a></div>



    @else

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!! trans('custom.user_have_invited_you_to_the_company_text_by_role', ['sender_email' => '<a href="mailto:'.$opts['sender_email'].'">'.$opts['sender_email'].'</a>', 'sender_name' => $opts['sender_name'], 'role' => trans('custom.art_'.$opts['role']), 'company_name' => $opts['company_title']], $opts['lang'])!!}</div>


        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"> <a href="{{env('APP_URL').'#/'.$opts['company_id'].'/projects'}}" style="
    display: block;
    color: black;
    text-transform: uppercase;
    text-decoration:none;
    background: #FCC537;
    width: fit-content;
    padding: 2% 8%;
    border-radius: 100px;
    margin: auto;
    font-size: 16px;
">{{trans('custom.company_join_validation.view_projects',[], $opts['lang'])}}</a></div>


    @endif

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>



@endsection