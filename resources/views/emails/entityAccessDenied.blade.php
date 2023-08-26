@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/access_denied_email.png')}}">


    @if($opts['entity'] == 'project')

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.the_administrator_of_the_project_closed_access_to_project', ['project_name' => $opts['project_name'],'admin_email' => $opts['admin_email']], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.if_you_have_questions_regarding_this', [], $opts['lang'])}}</div>

        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="mailto:'{{$opts['admin_email']}}'" style="
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
">{{trans('custom.contact_administrator',[], $opts['lang'])}}</a></div>

    @else

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.the_administrator_of_the_company_closed_access_to_projects', ['company_title' => $opts['company_title'], 'admin_email' => $opts['admin_email']], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.if_you_have_questions_regarding_this', [], $opts['lang'])}}</div>

        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="mailto:'{{$opts['admin_email']}}'" style="
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
">{{trans('custom.contact_administrator',[], $opts['lang'])}}</a></div>

    @endif

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>

@endsection