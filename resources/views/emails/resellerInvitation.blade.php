<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Reseller Portal Access</title>
</head>
<body style="margin:0;padding:0;background-color:#f9f9f9;font-family:'Lato',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9f9f9;">
    <tr>
        <td align="center" style="padding:40px 20px;">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#000000;border-radius:8px;">
                <tr>
                    <td style="padding:40px;">
                        <h1 style="color:#ffffff;font-size:24px;text-align:center;margin:0 0 24px;">Welcome to Living Legacy</h1>
                        <p style="color:#ecf0f1;font-size:16px;line-height:1.6;margin:0 0 16px;">Hello {{ $data['name'] }},</p>
                        <p style="color:#ecf0f1;font-size:16px;line-height:1.6;margin:0 0 24px;">Your reseller account has been approved. Click the button below to set your password and access your Reseller Portal.</p>
                        <p style="text-align:center;margin:24px 0;">
                            <a href="{{ $data['loginLink'] }}" target="_blank" style="display:inline-block;padding:15px 40px;background-color:#18163a;color:#ffffff !important;text-decoration:none;font-size:18px;border-radius:4px;">Set Password & Access Portal</a>
                        </p>
                        <p style="color:#bdc3c7;font-size:14px;line-height:1.6;margin:24px 0 0;">This link expires in 7 days. You will be asked to create a password before logging in. If you need a new link, please contact your administrator.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
