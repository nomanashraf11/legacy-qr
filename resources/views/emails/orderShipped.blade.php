@extends('emails.layout')
@section('title', 'Order Shipment')
@section('heading', 'Order Shipment')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] }}</strong> has been shipped and is on its way to you.</p>
    @if(!empty($data['shippingCarrier']) || !empty($data['tracking']))
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Shipping details:</strong></p>
    @if(!empty($data['shippingCarrier']))
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 4px;"><strong>Carrier:</strong> {{ $data['shippingCarrier'] }}</p>
    @endif
    @if(!empty($data['tracking']))
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 4px;"><strong>Tracking ID:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;font-family:monospace;background:#f3f4f6;padding:12px;border-radius:6px;">{{ $data['tracking'] }}</p>
    @endif
    @else
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">You can find tracking details in your Partner Portal under My Orders or Invoices once they become available.</p>
    @endif
    @if(!empty($data['trackingDetails']))
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Notes:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 24px;">{{ $data['trackingDetails'] }}</p>
    @endif
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0;">Thank you for your business.</p>
@endsection
