<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
</head>
<body style="margin:0; padding:24px; font-family:Arial, sans-serif; background:#f8fafc; color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
        <tr>
            <td style="padding:24px;">
                <h1 style="margin:0 0 12px; font-size:22px; line-height:1.3;">
                    Welcome to {{ config('app.name') }}
                </h1>
                <p style="margin:0 0 10px; font-size:15px; line-height:1.6;">
                    Hello {{ $user->name ?: 'Learner' }},
                </p>
                <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                    Your account is now active. You can start enrolling in courses and tracking your learning progress.
                </p>
                @if(config('app.url'))
                <p style="margin:0;">
                    <a href="{{ config('app.url') }}"
                        style="display:inline-block; padding:10px 16px; border-radius:8px; background:#06b6d4; color:#082f49; text-decoration:none; font-weight:700;">
                        Open {{ config('app.name') }}
                    </a>
                </p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
