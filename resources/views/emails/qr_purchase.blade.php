@extends('emails.layout')
@section('title', 'Order Confirmation')
@section('heading', 'Purchase Receipt')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Hello {{ $data['userName'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Thank you for your order. Here are the details:</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 8px;"><strong>QR codes:</strong> {{ $data['qr_codes'] }}</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 24px;"><strong>Amount paid:</strong> ${{ number_format((float) $data['amount'], 2) }}</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0;">We’ll process your order and be in touch with tracking information.</p>
@endsection
