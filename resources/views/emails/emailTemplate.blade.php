<!DOCTYPE html>
<html>

<body style="margin:0 auto;background:#fff;font-family:'Open Sans',sans-serif;color:#0F3238;max-width:600px">

<header style="height:calc(5em + 4vw)">
    <img style="margin:0;width: 100%;position: relative;display: block;" src="{{ asset('images/email/header_email_bg.png')}}">
</header>
<div class="content">
    @yield('email_title')
    @yield('email_content')

</div>

<footer>
    <div style="text-align: center; margin-top:8%">{{trans('custom.thank_you_for_being_with_us', [], $lang??'en')}}</div>
    <div style="text-align: center;"><b>{{trans('custom.my3dcloud_team', [], $lang??'en')}}</b></div>
    <div style="background:#55BED0;padding-top:28px;margin-top:32px;padding-bottom: 46px;">
        <div style="margin-bottom: 16px">
            <div style="width:fit-content;margin:auto">
                <a target="_blank" href="https://www.facebook.com/my3dcloud" style="margin-left: -9px;"><img
                            src="{{ asset('images/email/fb_email_icon.png')}}"></a>
                <a target="_blank" href="https://t.me/my3dcloud"><img src="{{ asset('images/email/tg_email_icon.png')}}" style="margin-left: 10px; margin-right: 17px;"></a>
                <a target="_blank" href="https://www.linkedin.com/company/my3dcloud"><img
                            src="{{ asset('images/email/li_email_icon.png')}}"></a>
            </div>
        </div>
        <div style="text-align: center;color:#ffffff">{{env('APP_NAME')}} @ 2021</div>
        <div style="text-align: center;color:#ffffff">Ziedu street 5-34, Liepaja, LV-3405, Latvia | <a
                    style="color: white !important;" href="https://my3d.cloud" target="_blank">my3d.cloud</a></div>
    </div>
</footer>
</body>

</html>