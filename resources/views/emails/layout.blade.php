<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Living Legacy')</title>
    <style type="text/css">
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        table { border-collapse: collapse; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .card { background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header-bar { background: #000000; padding: 24px; text-align: center; }
        .logo-cell { padding: 0; text-align: center; }
        .logo { max-width: 160px; height: auto; display: block; margin: 0 auto; }
        .title-cell { padding: 24px 24px 8px; text-align: center; background-color: #ffffff; }
        .title { font-size: 22px; font-weight: 600; color: #1a365d; margin: 0; }
        .body-cell { padding: 0 32px 32px; background-color: #ffffff; }
        .body-text { font-size: 16px; line-height: 1.6; color: #4b5563; margin: 0 0 16px; }
        .body-text strong { color: #1f2937; }
        .footer-cell { padding: 20px 24px; background-color: #f9fafb; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer-text { font-size: 13px; color: #6b7280; margin: 0; }
        .btn { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #0f66a9 0%, #1a7fc9 100%); color: #ffffff !important; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 8px; margin: 8px 0 24px; box-shadow: 0 2px 8px rgba(15, 102, 169, 0.3); }
        .btn:hover { background: linear-gradient(135deg, #0d5a94 0%, #1569a8 100%); }
        .link { color: #0f66a9; text-decoration: none; font-weight: 500; }
        .link:hover { text-decoration: underline; }
        .muted { font-size: 14px; color: #6b7280; margin-top: 20px; }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fa;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;">
<tr><td style="padding:32px 16px;">
<table width="600" cellpadding="0" cellspacing="0" align="center" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden;">
<tr>
    <td style="background:#000000;padding:28px 24px;text-align:center;">
        <img src="{{ config('app.mail_logo_url') }}" alt="Living Legacy" width="160" style="max-width:160px;height:auto;display:block;margin:0 auto;border:0;">
    </td>
</tr>
<tr>
    <td style="padding:24px 24px 8px;text-align:center;background-color:#ffffff;">
        <h1 style="font-size:22px;font-weight:600;color:#1a365d;margin:0;">@yield('heading')</h1>
    </td>
</tr>
<tr>
    <td style="padding:0 32px 32px;background-color:#ffffff;">
        @yield('body')
    </td>
</tr>
<tr>
    <td style="padding:20px 24px;background-color:#f9fafb;text-align:center;border-top:1px solid #e5e7eb;">
        <p style="font-size:13px;color:#6b7280;margin:0;">Living Legacy · Preserving Memories</p>
    </td>
</tr>
</table>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
