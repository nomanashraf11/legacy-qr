@extends('emails.layout')
@section('title', 'New Order')
@section('heading', 'New Order Waiting')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">A new order has been placed and is waiting for processing.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Order #:</strong> {{ $data['orderNumber'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Reseller:</strong> {{ $data['resellerName'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Items:</strong> {{ $data['items'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;"><strong>Amount:</strong> ${{ $data['amount'] ?? '0.00' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;"><a href="{{ $data['adminUrl'] ?? url('/orders') }}" style="color:#0f66a9;text-decoration:none;font-weight:500;">View orders →</a></p>
@endsection
