<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:32px 16px; background:#f3f6fa; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%; max-width:600px; border-collapse:separate; border-spacing:0; background:#ffffff; border:1px solid #dbe3ee; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px 36px; border-bottom:1px solid #e5eaf1;">
                            <img src="{{ asset('images/logo-sinergi.png') }}" width="48" height="48" alt="" style="display:inline-block; width:48px; height:48px; vertical-align:middle;">
                            <span style="display:inline-block; margin-left:10px; color:#111827; font-size:17px; font-weight:700; letter-spacing:3px; vertical-align:middle;">SINERGI STORE</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px;">
                            <p style="margin:0 0 10px; color:#c2410c; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Informasi pembeli</p>
                            <h1 style="margin:0 0 18px; color:#111827; font-size:28px; line-height:1.25;">{{ $heading }}</h1>
                            <p style="margin:0 0 14px; color:#334155; font-size:16px; line-height:1.7;">Halo {{ $customerName ?: 'Pelanggan' }},</p>
                            <p style="margin:0 0 22px; color:#475569; font-size:15px; line-height:1.7;">{{ $messageBody }}</p>

                            @if ($details !== [])
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 26px; border-collapse:collapse; background:#f8fafc; border:1px solid #e2e8f0;">
                                    @foreach ($details as $label => $value)
                                        <tr>
                                            <td style="padding:11px 14px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:13px; line-height:1.5;">{{ $label }}</td>
                                            <td align="right" style="padding:11px 14px; border-bottom:1px solid #e2e8f0; color:#1e293b; font-size:13px; font-weight:700; line-height:1.5;">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if ($actionUrl && $actionLabel)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px; border-collapse:collapse;">
                                    <tr>
                                        <td align="center" bgcolor="#f59e0b" style="border-radius:10px;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block; padding:14px 24px; color:#111827; font-size:15px; font-weight:700; text-decoration:none;">{{ $actionLabel }}</a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:0 0 8px; color:#64748b; font-size:13px; line-height:1.6;">Jika tombol tidak berfungsi, salin tautan berikut ke browser:</p>
                                <p style="margin:0 0 24px; overflow-wrap:anywhere; font-size:13px; line-height:1.6;"><a href="{{ $actionUrl }}" style="color:#b45309;">{{ $actionUrl }}</a></p>
                            @endif

                            <p style="margin:0; color:#64748b; font-size:13px; line-height:1.6;">Email ini dikirim otomatis sebagai informasi dari Sinergi Store.</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 24px; border-top:1px solid #e5eaf1; color:#64748b; font-size:12px; line-height:1.6;">
                            Sinergi Store · Email otomatis, mohon tidak membalas pesan ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
