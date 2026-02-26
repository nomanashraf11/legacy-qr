@extends('emails.layout')
@section('title', 'Application Received')
@section('heading', 'Thank You')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Thank you for your interest in becoming a Living Legacy reseller partner.</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">We’ve received your application and will review it shortly. Our team will be in touch with next steps.</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0;">If you have any questions in the meantime, don’t hesitate to reach out.</p>
@endsection
