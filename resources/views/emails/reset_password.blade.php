@extends('emails.layout')
@section('title', 'Reset Your Password')
@section('heading', 'Reset Your Password')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Hello,</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">We received a request to reset your password. Use the verification code below to complete the process:</p>
    <p style="text-align:center;margin:24px 0;">
        <span style="display:inline-block;padding:16px 32px;background-color:#f3f4f6;color:#1f2937;font-size:24px;font-weight:700;letter-spacing:4px;border-radius:8px;font-family:monospace;">{{ $otp }}</span>
    </p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">Enter this code in the password reset form to set your new password.</p>
    <p style="font-size:14px;line-height:1.5;color:#6b7280;margin:24px 0 0;">This code expires in 15 minutes. If you did not request a password reset, you can safely ignore this email.</p>
@endsection
