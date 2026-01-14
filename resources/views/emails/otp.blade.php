<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $purpose }} OTP</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f5f7;
            color: #1c1c1e;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        }
        .code {
            font-size: 32px;
            letter-spacing: 0.25em;
            text-align: center;
            font-weight: 700;
            color: #0f172a;
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 16px 0;
            margin: 24px 0;
        }
        .meta {
            font-size: 14px;
            color: #64748b;
            margin-top: 12px;
        }
        .footer {
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
            margin-top: 32px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <p style="font-size:14px;letter-spacing:0.3em;color:#94a3b8;text-transform:uppercase;margin-bottom:4px;">ECR Security</p>
        <h1 style="margin-top:0;font-size:24px;color:#0f172a;">{{ $purpose }} Verification</h1>
        <p>Hello,</p>
        <p>Your one-time password (OTP) for <strong>{{ strtolower($purpose) }}</strong> is:</p>

        <div class="code">{{ implode(' ', str_split($code)) }}</div>

        <p>This code is valid for <strong>{{ $expiresInMinutes }}</strong> minute{{ $expiresInMinutes > 1 ? 's' : '' }}.</p>

        @if (!empty($meta['context']))
            <div class="meta">
                Request context: {{ $meta['context'] }}
            </div>
        @endif

        <p>If you didn't request this OTP, please ignore this email. Your account remains secure.</p>

        <div class="footer">
            This is an automated message sent from the ECR backend.
        </div>
    </div>
</body>
</html>
