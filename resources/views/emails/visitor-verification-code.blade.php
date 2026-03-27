<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
</head>
<body style="margin:0;padding:0;background:#f4f8fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:620px;margin:0 auto;padding:24px 16px;">
        <div style="background:#ffffff;border:1px solid #e5edf5;border-radius:20px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#96defb 0%,#2a93cc 100%);padding:26px 24px;color:#fff;">
                <h1 style="margin:0 0 8px;font-size:28px;">Verify your account</h1>
                <p style="margin:0;line-height:1.6;">Use the verification code below to activate your visitor account.</p>
            </div>

            <div style="padding:24px;">
                <p style="margin:0 0 14px;">Hello {{ $user->name ?: 'Visitor' }},</p>
                <p style="margin:0 0 18px;line-height:1.7;">
                    To keep your account secure, please enter the verification code below on the verification page after signing in.
                </p>

                <div style="margin:0 0 18px;padding:18px;border-radius:16px;background:#f8fbfd;border:1px solid #e5edf5;text-align:center;">
                    <div style="font-size:34px;letter-spacing:8px;font-weight:700;color:#114b70;">{{ $code }}</div>
                    <div style="margin-top:10px;font-size:14px;color:#64748b;">This code expires in {{ $expiresInMinutes }} minutes.</div>
                </div>

                <p style="margin:0;line-height:1.7;color:#475467;">
                    If you did not create this account, you can safely ignore this email.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
