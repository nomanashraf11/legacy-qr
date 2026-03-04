@extends('emails.layout')
@section('title', 'Order Confirmation')
@section('heading', 'Order Confirmation')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] }}</strong> has been accepted by our team and is being prepared for shipment.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">We will notify you about the carrier and tracking details when your package ships. You can also find your tracking ID in your <a href="{{ $data['portalUrl'] ?? '#' }}" style="color:#0f66a9;text-decoration:none;font-weight:500;">Partner Portal</a> under My Orders or Invoices once it becomes available.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">You can view your invoice online here: <a href="{{ $data['invoiceUrl'] ?? '#' }}" style="color:#0f66a9;text-decoration:none;font-weight:500;">View Invoice</a>.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0;">Thank you for your business.</p>
@endsection
