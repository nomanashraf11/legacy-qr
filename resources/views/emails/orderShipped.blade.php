@extends('emails.layout')
@section('title', 'Order Shipped')
@section('heading', 'Order Shipped')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] }}</strong> is now in transit.</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 8px;"><strong>Tracking ID:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">{{ $data['tracking'] ?? '—' }}</p>
    @if(!empty($data['trackingDetails']))
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 8px;"><strong>Details:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 24px;">{{ $data['trackingDetails'] }}</p>
    @endif
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0;">Thank you for your business.</p>
@endsection
