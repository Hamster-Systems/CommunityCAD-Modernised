<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SMTP test</title></head>
<body style="margin:0;padding:0;background:#edf2f8;color:#18283b;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#edf2f8;"><tr><td align="center" style="padding:38px 16px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;border-collapse:separate;border-spacing:0;background:#ffffff;border:1px solid #dfe6ef;border-radius:14px;">
@include('emails.partials.brand-header')
<tr><td style="padding:34px 32px 28px;">
  <div style="display:inline-block;background:#e6f8ef;color:#176b43;border-radius:100px;padding:7px 12px;font-size:12px;font-weight:700;">SMTP CONNECTION TEST</div>
  <h1 style="font-size:27px;line-height:35px;margin:21px 0 12px;color:#14263b;">Your email is working!</h1>
  <p style="font-size:15px;line-height:25px;color:#394b62;margin:0 0 22px;">This message was sent by {{ $communityName }} using your configured SMTP service. Receiving it confirms the message reached this inbox.</p>
  <table role="presentation" width="100%" style="background:#f0f6fc;border-radius:10px;" cellpadding="0" cellspacing="0"><tr><td style="padding:18px 20px;font-size:14px;line-height:23px;color:#29445e;"><strong>Test successful</strong><br>Your outgoing email configuration is ready for further testing of account verification and notifications.</td></tr></table>
  <p style="font-size:13px;line-height:22px;margin:22px 0 0;color:#61738a;">You don't need to reply to this test message.</p>
</td></tr>
@include('emails.partials.brand-footer')
</table></td></tr></table></body></html>