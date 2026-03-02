@extends('emails.layout')
@section('title', 'New Reseller Application')
@section('heading', 'New Reseller Application')
@section('body')
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;">A new reseller has applied to partner with Living Legacy.</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Name:</strong> {{ $data['fullName'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Business:</strong> {{ $data['businessName'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 8px;"><strong>Email:</strong> {{ $data['email'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;"><strong>Phone:</strong> {{ $data['phone'] ?? '—' }}</p>
    <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 16px;"><a href="{{ $data['adminUrl'] ?? url('/reseller-applications') }}" style="color:#0f66a9;text-decoration:none;font-weight:500;">Review application →</a></p>
@endsection
