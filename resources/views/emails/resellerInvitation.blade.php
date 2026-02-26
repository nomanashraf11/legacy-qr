@extends('emails.layout')
@section('title', 'Your Reseller Portal Access')
@section('heading', 'Welcome to Living Legacy')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 16px;">Hello {{ $data['name'] }},</p>
    <p style="font-size:16px;line-height:1.6;color:#e5e5e5;margin:0 0 24px;">Your reseller account has been approved. Set your password using the link below to access your Reseller Portal and start ordering.</p>
    <p style="text-align:center;margin:24px 0;">
        <a href="{{ $data['loginLink'] }}" target="_blank" style="display:inline-block;padding:14px 32px;background-color:#0f66a9;color:#ffffff !important;text-decoration:none;font-size:16px;font-weight:600;border-radius:8px;">Set Password & Access Portal</a>
    </p>
    <p style="font-size:14px;line-height:1.5;color:#9ca3af;margin:24px 0 0;">This link expires in 7 days. You’ll choose a password before logging in. Need a new link? Contact your administrator.</p>
@endsection
