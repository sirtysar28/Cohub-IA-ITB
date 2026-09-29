{{-- Template email generik CoHub — desain modern, inline CSS, aman untuk mayoritas email client.
    Variabel: $accent, $icon, $pretitle, $title, $intro[], $details[label=>value], $ctaLabel, $ctaUrl, $note --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>{{ $title ?? 'Notifikasi CoHub' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
@php
    $accent  = $accent ?? '#4f46e5';
    $accent2 = $accent2 ?? '#7c3aed';
@endphp

<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $pretitle ?? 'Notifikasi CoHub IA ITB Jatim' }}</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:24px 12px;">
<tr><td align="center">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:100%;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(15,23,42,.10);">

        {{-- ===== HEADER ===== --}}
        <tr>
            <td style="background-color:#312e81;background-image:linear-gradient(135deg,#4f46e5 0%,#7c3aed 55%,#a855f7 100%);padding:28px 32px;text-align:center;">
                <div style="font-size:34px;line-height:1;">{{ $icon ?? '🏢' }}</div>
                <div style="color:#ffffff;font-size:20px;font-weight:800;letter-spacing:.3px;margin-top:8px;">
                    CoHub <span style="font-weight:400;opacity:.85;">IA ITB Jatim</span>
                </div>
                <div style="display:inline-block;margin-top:10px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:5px 16px;">
                    <span style="color:#e0e7ff;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">{{ $pretitle ?? 'Notifikasi' }}</span>
                </div>
            </td>
        </tr>

        {{-- ===== BODY ===== --}}
        <tr>
            <td style="padding:36px 32px 8px;">
                <h1 style="margin:0 0 14px;font-size:22px;line-height:1.35;color:#0f172a;font-weight:800;">{{ $title }}</h1>

                @foreach (($intro ?? []) as $p)
                    <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#475569;">{{ $p }}</p>
                @endforeach
            </td>
        </tr>

        @if (!empty($details))
        <tr>
            <td style="padding:8px 32px 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                    @foreach ($details as $label => $value)
                    <tr style="background-color:{{ $loop->odd ? '#f8fafc' : '#ffffff' }};">
                        <td style="padding:11px 18px;font-size:13px;color:#64748b;width:42%;border-bottom:1px solid #eef2f7;">{{ $label }}</td>
                        <td style="padding:11px 18px;font-size:13px;color:#0f172a;font-weight:600;text-align:right;border-bottom:1px solid #eef2f7;">{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif

        {{-- ===== CTA ===== --}}
        @if (!empty($ctaUrl))
        <tr>
            <td align="center" style="padding:30px 32px 6px;">
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-radius:12px;background-color:#4f46e5;background-image:linear-gradient(135deg,#4f46e5,#7c3aed);box-shadow:0 6px 16px rgba(79,70,229,.35);">
                            <a href="{{ $ctaUrl }}" style="display:inline-block;padding:14px 38px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:12px;letter-spacing:.3px;">
                                {{ $ctaLabel ?? 'Buka Aplikasi' }} →
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="margin:14px 0 0;font-size:12px;color:#94a3b8;">Tombol tidak berfungsi? Salin tautan ini ke browser:<br>
                    <span style="color:#6366f1;word-break:break-all;">{{ $ctaUrl }}</span></p>
            </td>
        </tr>
        @endif

        @if (!empty($note))
        <tr>
            <td style="padding:18px 32px 30px;">
                <div style="background:#eef2ff;border-left:4px solid {{ $accent }};border-radius:8px;padding:12px 16px;font-size:13px;line-height:1.6;color:#4338ca;">💡 {{ $note }}</div>
            </td>
        </tr>
        @endif

        {{-- ===== FOOTER ===== --}}
        <tr>
            <td style="background:#0f172a;padding:26px 32px;text-align:center;">
                <p style="margin:0;color:#e2e8f0;font-weight:700;font-size:14px;">🏢 CoHub IA ITB Jatim</p>
                <p style="margin:6px 0 0;color:#94a3b8;font-size:12px;">Platform sewa properti &amp; jasa profesional</p>
                <p style="margin:2px 0 0;color:#64748b;font-size:11px;">https://cohubiaitbjatim.com</p>
                <p style="margin:14px 0 0;border-top:1px solid #1e293b;padding-top:14px;color:#475569;font-size:11px;">
                    Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.<br>
                    &copy; {{ date('Y') }} CoHub IA ITB Jatim — All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</td></tr>
</table>
</body>
</html>
