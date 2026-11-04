<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Atur ulang password Sinergi Store</title>
</head>
<body style="margin:0; padding:32px 16px; background:#f3f6fa; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%; max-width:600px; border-collapse:separate; border-spacing:0; background:#ffffff; border:1px solid #dbe3ee; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px 36px; border-bottom:1px solid #e5eaf1;">
                            <img src="{{ $message->embed(public_path('images/logo-sinergi.png')) }}" width="48" height="48" alt="Sinergi Store" style="display:inline-block; width:48px; height:48px; vertical-align:middle;">
                            <span style="display:inline-block; margin-left:10px; color:#111827; font-size:17px; font-weight:700; letter-spacing:3px; vertical-align:middle;">SINERGI STORE</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px;">
                            <p style="margin:0 0 10px; color:#c2410c; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Pemulihan akun</p>
                            <h1 style="margin:0 0 18px; color:#111827; font-size:28px; line-height:1.25;">Atur ulang password</h1>
                            <p style="margin:0 0 14px; color:#334155; font-size:16px; line-height:1.7;">Halo {{ $customerName ?: 'Pelanggan' }},</p>
                            <p style="margin:0 0 26px; color:#475569; font-size:15px; line-height:1.7;">Kami menerima permintaan untuk mengatur ulang password akun Sinergi Store kamu. Tekan tombol di bawah untuk membuat password baru.</p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 26px; border-collapse:collapse;">
                                <tr>
                                    <td align="center" bgcolor="#f59e0b" style="border-radius:10px;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block; padding:14px 24px; color:#111827; font-size:15px; font-weight:700; text-decoration:none;">Atur password baru</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px; color:#64748b; font-size:13px; line-height:1.6;">Tautan ini berlaku selama {{ $expireMinutes }} menit. Jika tombol tidak berfungsi, salin tautan berikut ke browser:</p>
                            <p style="margin:0 0 24px; overflow-wrap:anywhere; font-size:13px; line-height:1.6;"><a href="{{ $resetUrl }}" style="color:#b45309;">{{ $resetUrl }}</a></p>
                            <p style="margin:0; color:#64748b; font-size:13px; line-height:1.6;">Kalau kamu tidak meminta pengaturan ulang password, abaikan email ini. Password akunmu tidak akan berubah.</p>
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
