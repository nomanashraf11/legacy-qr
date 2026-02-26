<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Living Legacy')</title>
    <style type="text/css">
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        table { border-collapse: collapse; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .card { background-color: #1a1a1a; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.12); }
        .logo-cell { padding: 28px 24px 20px; text-align: center; }
        .logo { max-width: 160px; height: auto; display: block; margin: 0 auto; }
        .title-cell { padding: 8px 24px 24px; text-align: center; }
        .title { font-size: 22px; font-weight: 600; color: #ffffff; margin: 0; }
        .body-cell { padding: 0 32px 32px; }
        .body-text { font-size: 16px; line-height: 1.6; color: #e5e5e5; margin: 0 0 16px; }
        .body-text strong { color: #fff; }
        .footer-cell { padding: 16px 24px; background-color: #0d0d0d; text-align: center; }
        .footer-text { font-size: 13px; color: #888; margin: 0; }
        .btn { display: inline-block; padding: 14px 32px; background-color: #0f66a9; color: #ffffff !important; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 8px; margin: 8px 0 24px; }
        .muted { font-size: 14px; color: #9ca3af; margin-top: 20px; }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5;">
<tr><td style="padding:32px 16px;">
<table width="600" cellpadding="0" cellspacing="0" align="center" style="max-width:600px;margin:0 auto;">
<tr><td>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a;border-radius:12px;">
<tr>
    <td style="padding:28px 24px 20px;text-align:center;">
        <img src="{{ config('mail.logo_url') ?? \Illuminate\Support\Str::replaceFirst('http://', 'https://', asset('images/logo/Living_Legacy-logos_white.png')) }}" alt="Living Legacy" width="160" style="max-width:160px;height:auto;display:block;border:0;">
    </td>
</tr>
<tr>
    <td style="padding:8px 24px 24px;text-align:center;">
        <h1 style="font-size:22px;font-weight:600;color:#ffffff;margin:0;">@yield('heading')</h1>
    </td>
</tr>
<tr>
    <td style="padding:0 32px 32px;">
        @yield('body')
    </td>
</tr>
<tr>
    <td style="padding:16px 24px;background-color:#0d0d0d;text-align:center;">
        <p style="font-size:13px;color:#888;margin:0;">Living Legacy</p>
    </td>
</tr>
</table>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
