<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Living Legacy Partner Portal</title>
    <style type="text/css">
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        table { border-collapse: collapse; }
        .wrapper { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fa;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;">
<tr><td style="padding:32px 16px;">
<table width="600" cellpadding="0" cellspacing="0" align="center" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:0;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
    <!-- Black header with logo and Welcome to the Team -->
    <tr>
        <td style="background:#000000;padding:32px 24px;text-align:center;">
            <img src="{{ \App\Constants\MailConstants::LOGO_URL }}" alt="Living Legacy" width="160" style="max-width:160px;height:auto;display:block;margin:0 auto 20px;border:0;">
            <h1 style="font-size:28px;font-weight:700;color:#ffffff;margin:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">Welcome to the Team</h1>
        </td>
    </tr>
    <!-- Body -->
    <tr>
        <td style="padding:32px 40px;background-color:#ffffff;">
            <p style="font-size:18px;line-height:1.6;color:#1f2937;margin:0 0 16px;"><strong>Congratulations {{ $data['name'] }}!</strong></p>
            <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 24px;">Your application for <strong>{{ $data['business_name'] ?? 'Living Legacy' }}</strong> has been officially approved. You now have full access to our wholesale catalog and order management system.</p>
            <p style="font-size:16px;line-height:1.6;color:#4b5563;margin:0 0 24px;">Please activate your account below to get started:</p>

            <!-- CTA Button -->
            <p style="text-align:center;margin:24px 0;">
                <a href="{{ $data['loginLink'] }}" target="_blank" style="display:inline-block;padding:16px 40px;background:#000000;color:#ffffff !important;text-decoration:none;font-size:14px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">Activate My Portal Account</a>
            </p>

            <p style="font-size:14px;line-height:1.5;color:#6b7280;margin:24px 0 0;text-align:center;">Already have an account? <a href="{{ url('/reseller-login') }}" style="color:#2563eb;text-decoration:underline;">Log in to your portal</a></p>
        </td>
    </tr>
    <!-- What You Get Access To -->
    <tr>
        <td style="padding:0 40px 32px;background-color:#ffffff;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:8px;padding:24px;">
                <tr><td style="text-align:center;padding-bottom:20px;">
                    <h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0;">What You Get Access To</h2>
                </td></tr>
                <tr><td>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="50%" style="padding:0 12px 16px 0;vertical-align:top;">
                                <p style="font-size:14px;line-height:1.5;color:#1f2937;margin:0 0 4px;"><strong>✓ Wholesale Pricing</strong></p>
                                <p style="font-size:13px;line-height:1.4;color:#6b7280;margin:0;">Exclusive partner rates on all products</p>
                            </td>
                            <td width="50%" style="padding:0 0 16px 12px;vertical-align:top;">
                                <p style="font-size:14px;line-height:1.5;color:#1f2937;margin:0 0 4px;"><strong>✓ Marketing Materials</strong></p>
                                <p style="font-size:13px;line-height:1.4;color:#6b7280;margin:0;">Ready-to-use promotional assets</p>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="padding:0 12px 0 0;vertical-align:top;">
                                <p style="font-size:14px;line-height:1.5;color:#1f2937;margin:0 0 4px;"><strong>✓ Order Management</strong></p>
                                <p style="font-size:13px;line-height:1.4;color:#6b7280;margin:0;">Track and manage all your orders</p>
                            </td>
                            <td width="50%" style="padding:0 0 0 12px;vertical-align:top;">
                                <p style="font-size:14px;line-height:1.5;color:#1f2937;margin:0 0 4px;"><strong>✓ Dedicated Support</strong></p>
                                <p style="font-size:13px;line-height:1.4;color:#6b7280;margin:0;">Expert help whenever you need it</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </td>
    </tr>
    <!-- Questions -->
    <tr>
        <td style="padding:0 40px 24px;text-align:center;border-top:1px solid #e5e7eb;">
            <p style="font-size:14px;line-height:1.5;color:#6b7280;margin:24px 0 0;">Questions? Reply to this email or <a href="https://calendly.com/livinglegacyqr-info/30min" style="color:#2563eb;text-decoration:underline;">book an onboarding call</a></p>
        </td>
    </tr>
    <!-- Footer -->
    <tr>
        <td style="padding:24px 40px;background-color:#ffffff;text-align:center;border-top:1px solid #e5e7eb;">
            <p style="font-size:14px;font-weight:600;color:#1f2937;margin:0 0 4px;">Living Legacy QR Partner Program</p>
            <p style="font-size:13px;color:#6b7280;margin:0;">Preserve Memories. Celebrate Legacies.</p>
        </td>
    </tr>
</table>
</td></tr>
</table>
</body>
</html>
