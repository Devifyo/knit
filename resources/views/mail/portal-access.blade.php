<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#f4f4f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#18181b;">
    <div style="max-width:480px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e4e4e7;border-radius:14px;padding:28px;">
            <h1 style="margin:0 0 8px;font-size:18px;font-weight:600;">
                {{ $reset ? 'Reset your password' : "Welcome to {$workspace}" }}
            </h1>
            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#52525b;">
                @if ($reset)
                    We received a request to reset your {{ $workspace }} portal password. Click below to choose a new one.
                @else
                    You've been given access to the {{ $workspace }} customer portal, where you can view your deals, quotes, projects and support tickets. Set a password to get started.
                @endif
            </p>
            <a href="{{ $url }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:10px 18px;border-radius:8px;">
                {{ $reset ? 'Set a new password' : 'Activate your account' }}
            </a>
            <p style="margin:20px 0 0;font-size:12px;line-height:1.6;color:#a1a1aa;">
                If the button doesn't work, copy and paste this link:<br>
                <a href="{{ $url }}" style="color:#6366f1;word-break:break-all;">{{ $url }}</a>
            </p>
        </div>
        <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">Powered by Knit</p>
    </div>
</body>
</html>
