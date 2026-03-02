@extends('emails.layout')
@section('title', 'Order Placed')
@section('heading', 'Order Placed')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Thank you for your order. We've received it and will process it shortly.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Order #</strong> {{ $data['orderNumber'] }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Items:</strong> {{ $data['items'] }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 24px;"><strong>Total:</strong> ${{ $data['amount'] }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0;">We'll notify you when your order is accepted and shipped.</p>
@endsection
