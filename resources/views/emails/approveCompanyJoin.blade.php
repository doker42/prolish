@extends('emails.emailTemplate')


@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/join_company_request_email.png')}}">


    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{{trans('custom.company_join_validation.join_request', [], $lang)}}</div>
    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_join_validation.request', ['user_name' => $user->name, 'user_email' => $user->email, 'company_name' => $user->belongToCompany->title], $lang)}}</div>
    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.company_join_validation.to_approve', [], $lang)}}</div>
    <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"><a
                href="{{route('company_approve', ['token' => $user->verifyUser->token, 'action' => 'approve','lang'=>$lang], $lang)}}" style="
    display: block;
    color: black;
    text-transform: uppercase;
    background: #FCC537;
    text-decoration: none;
    width: fit-content;
    padding: 2% 8%;
    border-radius: 100px;
    margin: auto;
    font-size: 16px;
">{{trans('custom.company_join_validation.approve_request',[], $lang)}}</a></div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;"> {!!trans('custom.company_join_validation.if_you_want_to_contact_user_press_here', ['user_name'=>$user->name, 'here' => '<b><u><a style="color:#0F3238" href="mailto:'.$user->email.'">'.trans('custom.here',[], $lang).'</a></u></b>'], $lang)!!}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;"> {!!trans('custom.company_join_validation.if_you_want_to_reject_request_press_here', ['here' => '<b><u><a style="color:#0F3238" href="'.route("company_approve", ["token" =>  $user->verifyUser->token, "action" =>"decline", "lang"=>$lang], $lang).'">'.trans('custom.here',[], $lang).'</a></u></b>'], $lang)!!}</div>

    <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<b><u><a style="text-decoration:none;color:#0F3238" href="mailto:'.env('SUPPORT_EMAIL').'">'.env('APP_NAME').' Support</a></u></b>'], $lang)!!}</div>



@endsection