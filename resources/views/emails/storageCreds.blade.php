@extends('emails.emailTemplate')

@section('email_content')

    <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/verify_account_email.png')}}">


    <div style="text-align: center; font-size: calc(28px + 4*(100vw - 360px) / 1000);margin-top: 6.6%;margin-bottom: 6.6%;">{!! trans('custom.new_email_validation.thank_you_for_choosing_my3d', ['my_3d' => '<br><b><a style="text-decoration:none;color:#0F3238" href="https://my3d.cloud">'.env('APP_NAME').'!</a></b>'], $user->locale) !!}</div>

    <div style="padding-left: 12.3%;padding-right: 12.3%;">Storage LINK: <b>{{$link}}</b></div>
    <br>
    <div style="padding-left: 12.3%;padding-right: 12.3%;">Storage account: <b>{{$nickName}}</b></div>
    <br>
    <div style="padding-left: 12.3%;padding-right: 12.3%;">Storage password: <b>{{$password}}</b></div>
    <br>
    <div style="padding-left: 12.3%;padding-right: 12.3%;"> <a href="https://my3d.artreal.pro/webdav_instruction.pdf"><b>How to create local disk PDF</b></a></div>



@endsection