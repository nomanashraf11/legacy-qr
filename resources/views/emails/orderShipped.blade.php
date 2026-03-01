@extends('emails.layout')
@section('title', 'Order Shipped')
@section('heading', 'Order Shipped')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] }}</strong> has been shipped and is on its way.</p>
    @if(!empty($data['shippingCarrier']))
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Carrier:</strong> {{ $data['shippingCarrier'] }}</p>
    @endif
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Tracking ID:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;font-family:monospace;background:#f3f4f6;padding:12px;border-radius:6px;">{{ $data['tracking'] ?? '—' }}</p>
    @if(!empty($data['trackingDetails']))
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Notes:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 24px;">{{ $data['trackingDetails'] }}</p>
    @endif
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0;">Thank you for your business.</p>
@endsection
