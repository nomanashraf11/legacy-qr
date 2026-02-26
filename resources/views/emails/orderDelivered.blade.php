@extends('emails.layout')
@section('title', 'Order Delivered')
@section('heading', 'Order Delivered')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] ?? '—' }}</strong> has been delivered.</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 8px;"><strong>Tracking ID:</strong></p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 24px;">{{ $data['tracking'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0;">Thank you for your business.</p>
@endsection
