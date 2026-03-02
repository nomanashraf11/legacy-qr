@extends('emails.layout')
@section('title', 'Order Delivered')
@section('heading', 'Order Delivered')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Your order <strong>#{{ $data['orderNumber'] ?? '—' }}</strong> has been delivered.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0;">Thank you for your business.</p>
@endsection
