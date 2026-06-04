<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#f4f4f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#18181b;">
    <div style="max-width:480px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e4e4e7;border-radius:14px;padding:28px;">
            <h1 style="margin:0 0 8px;font-size:18px;font-weight:600;">You've been added to {{ $workspace }}</h1>
            <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#52525b;">
                An account has been created for you with the <strong>{{ $role }}</strong> role. Use these credentials to sign in, then change your password from Security settings.
            </p>
            <div style="background:#f4f4f5;border:1px solid #e4e4e7;border-radius:10px;padding:14px 16px;font-size:14px;margin-bottom:18px;">
                <div style="margin-bottom:6px;"><span style="color:#71717a;">Email</span><br><strong>{{ $email }}</strong></div>
                <div><span style="color:#71717a;">Temporary password</span><br><strong style="font-family:ui-monospace,Menlo,monospace;">{{ $password }}</strong></div>
            </div>
            <a href="{{ $loginUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:10px 18px;border-radius:8px;">Sign in</a>
            <p style="margin:20px 0 0;font-size:12px;line-height:1.6;color:#a1a1aa;">For your security, please change this password after your first sign-in.</p>
        </div>
        <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">Powered by Knit</p>
    </div>
</body>
</html>
