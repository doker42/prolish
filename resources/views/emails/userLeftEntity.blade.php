@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/user_leave_email.png')}}">


    @if($opts['entity'] == 'project')

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.user_has_left_the_project_from_now', ['user_name' => $opts['user_name'], 'project_name' => $opts['project_name']], $opts['lang'])}}</div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.you_can_review_all_users_of_the_project', [], $opts['lang'])}}</div>

        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="mailto:'{{ config('app.url').'/#/projects/'.$opts['project_id'].'/visibility'}}'" style="
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
">{{trans('custom.go_to_project',[], $opts['lang'])}}</a></div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $opts['lang'])!!}</div>


    @else

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.user_has_left_the_company_from_now', ['user_name' => $opts['user_name'], 'company_title' => $opts['company_title']], $opts['lang'])}}</div>
    @endif

@endsection