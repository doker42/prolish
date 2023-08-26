@extends('emails.emailTemplate')


@section('email_content')

    @if($action == 'approve')

        <img style="margin:auto;display:block;margin-top: 9%;"
             src="{{ asset('images/email/join_company_approve_email.png')}}">


        <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{trans('custom.company_join_validation.join_approve', [], $lang)}}</div>
        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_join_validation.approved', ['company_name' => $company], $lang)}}</div>
        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_join_validation.fo_the_authorization_click_button', [], $lang)}}</div>
        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="{{ Config::get('app.url') }}" style="
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
">{{trans('custom.company_join_validation.view_projects',[], $lang)}}</a></div>

        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $lang)!!}</div>



    @else

        <img style="margin:auto;display:block;margin-top: 9%;"
             src="{{ asset('images/email/join_company_refuse_email.png')}}">


        <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{trans('custom.company_join_validation.join_refuse', [], $lang)}}</div>
        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_join_validation.declined', ['company_name' => $company], $lang)}}</div>
        <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_join_validation.you_can_register_in_the_system', [], $lang)}}</div>
        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="mailto:{{env('ADMINISTRATOR_EMAIL')}}" style="
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
">{{trans('custom.contact_administrator',[], $lang)}}</a></div>
        <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                    href="mailto:{{env('ADMINISTRATOR_EMAIL')}}">{{env('ADMINISTRATOR_EMAIL')}}</a></div>


    @endif



@endsection