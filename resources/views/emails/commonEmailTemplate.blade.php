@extends('emails.emailTemplate')

@section('email_title')
{{isset($data['title'])?$data['title']:''}}
@endsection

@section('email_content')
{!! isset($data['message'])?nl2br($data['message']):'' !!}
@endsection
