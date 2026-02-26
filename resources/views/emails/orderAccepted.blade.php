@extends('emails.layout')
@section('title', 'Order Accepted')
@section('heading', 'Order Accepted')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] }}</strong> has been accepted by our team and is being prepared for dispatch.</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">We will notify you when it ships with tracking details.</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0;">Thank you for your business.</p>
@endsection
