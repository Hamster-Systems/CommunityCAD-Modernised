<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify your email</title></head>
<body style="margin:0;padding:0;background:#edf2f8;color:#18283b;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#edf2f8;"><tr><td align="center" style="padding:38px 16px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;border-collapse:separate;border-spacing:0;background:#ffffff;border:1px solid #dfe6ef;border-radius:14px;">
@include('emails.partials.brand-header')
<tr><td style="padding:34px 32px 28px;">
  <div style="font-size:12px;font-weight:700;color:#2666a0;letter-spacing:1.3px;">ACCOUNT SECURITY</div>
  <h1 style="font-size:27px;line-height:35px;margin:15px 0 12px;color:#14263b;">Verify your email address</h1>
  <p style="font-size:15px;line-height:25px;color:#394b62;margin:0 0 22px;">Thanks for creating a {{ $communityName }} account. Confirm your email address to activate password sign-in.</p>
  <table role="presentation" cellpadding="0" cellspacing="0"><tr><td bgcolor="#2563eb" style="border-radius:9px;background:#2563eb;"><a href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 23px;border-radius:9px;font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Verify email address</a></td></tr></table>
  <p style="font-size:13px;line-height:22px;color:#61738a;margin:23px 0 12px;">This secure link expires in <strong>24 hours</strong>. If you did not create this account, you can ignore this message.</p>
  <p style="font-size:12px;line-height:19px;color:#61738a;margin:0;">If the button doesn't work, copy this link into your browser:</p>
  <p style="font-size:12px;line-height:20px;overflow-wrap:anywhere;word-break:break-all;margin:6px 0 0;"><a href="{{ $verificationUrl }}" style="color:#2156a5;">{{ $verificationUrl }}</a></p>
</td></tr>
@include('emails.partials.brand-footer')
</table></td></tr></table></body></html>