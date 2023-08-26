@extends('emails.emailTemplate')


@section('email_content')

 <img style="margin:auto;display:block;margin-top: 9%;" src="{{ asset('images/email/welcome_to_3dcloud_email.png')}}">


 <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.welcome_ta_da_text',[], $user->locale)}}</div>
 <div style="font-size: 16px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{{trans('custom.everything_simple_here',[], $user->locale)}}</div>
 @if(!empty(env('DEMO_PROJECT_ID')) && env('DEMO_PROJECT_ID') > 0)

   <div style="font-size: 16px;
      margin-top: 6.6%;
      margin-bottom: 6.6%;
      padding-left: 12.3%;
      padding-right: 12.3%;">{{trans('custom.to_understand_possibilities',[], $user->locale)}}</div>
   <div style="text-align: center;margin-top: 4%;margin-bottom: 6.7%;"> <a href="{{env('APP_URL').'#/all/projects/'.env('DEMO_PROJECT_ID').'/items'}}" style="
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
">{{trans('custom.view_demo_project',[], $user->locale)}}</a></div>

 @endif

 <div style="font-size: 15px;
    margin-top: 6.6%;
    margin-bottom: 6.6%;
    padding-left: 12.3%;
    padding-right: 12.3%;">{!!trans('custom.if_you_have_any_difficulties_contact_support',['my_3d_support' => '<u><a style="text-decoration:none;color:#0F3238" href="support@my3d.cloud">My3D.Cloud!</a></u>'], $user->locale)!!}</div>





@endsection
