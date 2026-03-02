# Staging Email Troubleshooting Guide

## Analysis Summary

### Flow Overview

| Action                         | Controller Method         | DB Transaction                      | Email Trigger | Email Type        |
| ------------------------------ | ------------------------- | ----------------------------------- | ------------- | ----------------- |
| **Accept Order**               | `acceptOrder()`           | Commit **before** email             | After commit  | OrderAcceptedMail |
| **Mark as Delivered**          | `markAsDelivered()`       | Commit **before** email             | After commit  | OrderShippedMail  |
| **Add Tracking (IN_PROGRESS)** | `updateTrackingDetails()` | Commit **before** email (after fix) | After commit  | OrderShippedMail  |

### Previous Behavior (Root Cause)

1. **Email is synchronous** – `Mail::to()->send()` runs inline (no queue).
2. **Order status update succeeds** – DB is committed before email in accept/deliver flows.
3. **Email failure throws** – Any SMTP/SES error propagates and is caught by the controller.
4. **Generic error returned** – Catch block returned "Something went wrong" without logging the real exception.
5. **No visibility** – No logs to diagnose SMTP/SES/network issues.

### Code Changes Applied

-   **`sendOrderMail()` helper** – Wraps `Mail::send()` in try-catch, logs attempt + config + success/failure, never throws.
-   **API always succeeds** – Order status update always returns success; email failure no longer affects the response.
-   **Explicit logging** – `Order email: attempting to send`, `Order email: sent successfully`, `Order email: failed to send` with full error and stack trace.
-   **Transaction safety** – Email sent after `DB::commit()` so mail failure cannot rollback order changes.
-   **Rollback on DB errors** – If order update fails, transaction is rolled back and error is logged.

---

## Staging Checklist

### 1. Environment Variables (on EC2)

Verify `.env` on the staging server:

```bash
# Required for SMTP
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host      # e.g. email-smtp.us-east-1.amazonaws.com for SES
MAIL_PORT=587                  # or 465 for SSL, 2587 if 587 is blocked
MAIL_USERNAME=your-ses-smtp-user
MAIL_PASSWORD=your-ses-smtp-password
MAIL_ENCRYPTION=tls            # or ssl for 465
MAIL_FROM_ADDRESS=verified@yourdomain.com
MAIL_FROM_NAME="Living Legacy"

# If using SES directly (no SMTP)
# MAIL_MAILER=ses
# (SES uses AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION from .env)
```

### 2. AWS SES on Staging

-   [ ] **Sender/From** – `MAIL_FROM_ADDRESS` must be verified in SES (or domain verified).
-   [ ] **Recipient (sandbox)** – In SES sandbox, recipient emails must be verified. Production access removes this.
-   [ ] **EC2 outbound** – Ensure security group allows outbound on port 587 (SMTP) or 465.
-   [ ] **SES SMTP credentials** – Use SES SMTP credentials, not IAM keys, when `MAIL_MAILER=smtp`.

### 3. Network (EC2)

```bash
# Test SMTP connectivity from EC2
nc -zv email-smtp.us-east-1.amazonaws.com 587
# or
telnet email-smtp.us-east-1.amazonaws.com 587
```

If blocked, open outbound 587/465 in the security group.

### 4. Check Logs After Deployment

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Look for:
# "Order email: attempting to send" – confirms mail is attempted
# "Order email: sent successfully" – mail sent
# "Order email: failed to send" – includes error message and stack trace
# "Order accept failed" / "Order mark as delivered failed" – DB/other errors
```

### 5. Verify Config at Runtime

Add temporarily to a route or tinker:

```php
php artisan tinker
>>> config('mail.default')
>>> config('mail.mailers.smtp')
>>> config('mail.from')
```

---

## Common Staging Issues

| Issue                 | Symptom                                      | Fix                                                    |
| --------------------- | -------------------------------------------- | ------------------------------------------------------ |
| **SES sandbox**       | Emails not delivered to unverified addresses | Verify recipient emails in SES or move to production   |
| **Port blocked**      | Connection timeout                           | Allow outbound 587/465 in EC2 security group           |
| **Wrong credentials** | 535 auth error                               | Regenerate SES SMTP credentials and update .env        |
| **From not verified** | 554 or bounce                                | Verify MAIL_FROM_ADDRESS in SES                        |
| **Config cached**     | Old mail config used                         | `php artisan config:clear && php artisan config:cache` |

---

## Files Touched

-   `app/Http/Controllers/Admin/OrderController.php` – Email logging, resilient sending, transaction handling
