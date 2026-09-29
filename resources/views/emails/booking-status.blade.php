{{-- Email notifikasi perubahan status booking — CoHub IA ITB Jatim
    Variabel: $recipient, $booking, $from, $to, $actor, $headline, $tip, $url --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>[{{ $booking->code }}] {{ $headline }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
@php
    // Warna & ikon per status
    $meta = match ($to) {
        \App\Enums\BookingStatus::PendingConfirmation => ['⏱', '#f59e0b', '#fde68a'],
        \App\Enums\BookingStatus::Approved            => ['✅', '#3b82f6', '#bfdbfe'],
        \App\Enums\BookingStatus::Rejected            => ['❌', '#ef4444', '#fecaca'],
        \App\Enums\BookingStatus::Paid                => ['💳', '#6366f1', '#c7d2fe'],
        \App\Enums\BookingStatus::InProgress          => ['🔧', '#06b6d4', '#a5f3fc'],
        \App\Enums\BookingStatus::Completed           => ['🎉', '#10b981', '#a7f3d0'],
        \App\Enums\BookingStatus::Cancelled           => ['🚫', '#f43f5e', '#fecdd3'],
        default                                      => ['📄', '#6b7280', '#e5e7eb'],
    };
    [$statusIcon, $statusColor, $statusBg] = $meta;

    $isPartner = $recipient?->isOwner() || $recipient?->isProvider();

    // Timeline (hanya untuk jalur normal)
    $steps = ['draft', 'pending_confirmation', 'approved', 'paid', 'in_progress', 'completed'];
    $stepLabels = ['Draft', 'Konfirmasi', 'Disetujui', 'Dibayar', 'Berlangsung', 'Selesai'];
    $currentIndex = array_search($to->value, $steps);
    $showTimeline = $currentIndex !== false;
@endphp

<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $headline }}</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:24px 12px;">
<tr><td align="center">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:100%;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(15,23,42,.10);">

        {{-- ===== HEADER ===== --}}
        <tr>
            <td style="background-color:#312e81;background-image:linear-gradient(135deg,#4f46e5 0%,#7c3aed 55%,#a855f7 100%);padding:26px 32px;">
                <table role="presentation" width="100%">
                    <tr>
                        <td style="color:#ffffff;font-size:17px;font-weight:800;letter-spacing:.3px;">
                            🏢 CoHub <span style="font-weight:400;opacity:.85;">IA ITB Jatim</span>
                        </td>
                        <td align="right" style="color:#c7d2fe;font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;">
                            Notifikasi Booking
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ===== STATUS HERO ===== --}}
        <tr>
            <td align="center" style="padding:34px 32px 6px;">
                <div style="width:76px;height:76px;line-height:76px;margin:0 auto;background-color:{{ $statusBg }};border-radius:50%;font-size:36px;text-align:center;">{{ $statusIcon }}</div>
                <div style="display:inline-block;margin-top:14px;background:{{ $statusBg }};color:{{ $statusColor }};border-radius:999px;padding:7px 20px;font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase;">
                    {{ $to->label() }}
                </div>
                <h1 style="margin:18px 0 8px;font-size:22px;line-height:1.35;color:#0f172a;font-weight:800;">
                    Halo {{ explode(' ', trim($recipient?->name ?? 'Sahabat CoHub'))[0] }}, ada update booking!
                </h1>
                <p style="margin:0;font-size:15px;line-height:1.7;color:#475569;">{{ $headline }}</p>
            </td>
        </tr>

        {{-- ===== DETAIL BOOKING ===== --}}
        <tr>
            <td style="padding:26px 32px 0;">
                <p style="margin:0 0 10px;font-size:12px;font-weight:800;color:#94a3b8;letter-spacing:1.2px;text-transform:uppercase;">📦 Detail Booking</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                    <tr style="background-color:#f8fafc;">
                        <td style="padding:12px 18px;font-size:13px;color:#64748b;width:42%;border-bottom:1px solid #eef2f7;">Kode Booking</td>
                        <td style="padding:12px 18px;font-size:14px;color:#0f172a;font-weight:800;text-align:right;border-bottom:1px solid #eef2f7;font-family:'Consolas','Courier New',monospace;">{{ $booking->code }}</td>
                    </tr>
                    <tr style="background-color:#ffffff;">
                        <td style="padding:12px 18px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Jenis</td>
                        <td style="padding:12px 18px;font-size:13px;color:#0f172a;font-weight:600;text-align:right;border-bottom:1px solid #eef2f7;">
                            {{ $booking->type === \App\Enums\BookingType::Property ? '🏢 Sewa Properti' : '🛠 Jasa Profesional' }}
                        </td>
                    </tr>
                    <tr style="background-color:#f8fafc;">
                        <td style="padding:12px 18px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Item</td>
                        <td style="padding:12px 18px;font-size:13px;color:#0f172a;font-weight:600;text-align:right;border-bottom:1px solid #eef2f7;">{{ $booking->item_name }}</td>
                    </tr>
                    @if ($booking->start_date)
                    <tr style="background-color:#ffffff;">
                        <td style="padding:12px 18px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">
                            {{ $booking->type === \App\Enums\BookingType::Property ? 'Tanggal Sewa' : 'Jadwal' }}
                        </td>
                        <td style="padding:12px 18px;font-size:13px;color:#0f172a;font-weight:600;text-align:right;border-bottom:1px solid #eef2f7;">
                            {{ $booking->start_date->translatedFormat('d M Y') }}{{ $booking->end_date ? ' – '.$booking->end_date->translatedFormat('d M Y') : '' }}{{ $booking->schedule_time ? ' • '.$booking->schedule_time->format('H:i') : '' }}
                        </td>
                    </tr>
                    @endif
                    @if ($booking->type === \App\Enums\BookingType::Service && $booking->quantity > 1)
                    <tr style="background-color:#f8fafc;">
                        <td style="padding:12px 18px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Jumlah</td>
                        <td style="padding:12px 18px;font-size:13px;color:#0f172a;font-weight:600;text-align:right;border-bottom:1px solid #eef2f7;">{{ $booking->quantity }} {{ $booking->unit_label }}</td>
                    </tr>
                    @endif
                    <tr style="background-color:#ffffff;">
                        <td style="padding:12px 18px;font-size:13px;color:#64748b;">Total</td>
                        <td style="padding:12px 18px;font-size:16px;color:{{ $statusColor }};font-weight:800;text-align:right;">{{ format_idr($booking->total_price) }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ===== TIMELINE STATUS ===== --}}
        @if ($showTimeline)
        <tr>
            <td style="padding:26px 32px 0;">
                <p style="margin:0 0 14px;font-size:12px;font-weight:800;color:#94a3b8;letter-spacing:1.2px;text-transform:uppercase;">🧭 Progres Transaksi</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                    @foreach ($steps as $i => $step)
                        @php
                            $done = $i < $currentIndex;
                            $current = $i === $currentIndex;
                        @endphp
                        <td align="center" style="width:16.66%;padding:0 2px;">
                            <div style="width:26px;height:26px;line-height:26px;margin:0 auto;border-radius:50%;font-size:12px;font-weight:800;text-align:center;
                                background-color:{{ $done ? '#4f46e5' : ($current ? $statusColor : '#e2e8f0') }};
                                color:#ffffff;">{{ $done ? '✓' : ($current ? $statusIcon : $i + 1) }}</div>
                            <p style="margin:6px 0 0;font-size:9.5px;line-height:1.3;color:{{ $current ? $statusColor : ($done ? '#475569' : '#94a3b8') }};font-weight:{{ $current ? 800 : 500 }};">{{ $stepLabels[$i] }}</p>
                        </td>
                    @endforeach
                    </tr>
                </table>
            </td>
        </tr>
        @else
        <tr>
            <td style="padding:26px 32px 0;">
                <div style="background:#fef2f2;border-left:4px solid {{ $statusColor }};border-radius:8px;padding:12px 16px;font-size:13px;line-height:1.6;color:#991b1b;">
                    Transaksi ini berstatus <b>{{ $to->label() }}</b>.{{ !empty($tip) ? ' '.$tip : '' }}
                </div>
            </td>
        </tr>
        @endif

        {{-- ===== TIPS / CTA KONTEKSTUAL ===== --}}
        @if (!empty($tip) && $showTimeline)
        <tr>
            <td style="padding:18px 32px 0;">
                <div style="background:#eef2ff;border-left:4px solid #4f46e5;border-radius:8px;padding:12px 16px;font-size:13px;line-height:1.6;color:#4338ca;">💡 {{ $tip }}</div>
            </td>
        </tr>
        @endif

        {{-- ===== CTA ===== --}}
        <tr>
            <td align="center" style="padding:28px 32px 8px;">
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-radius:12px;background-color:#4f46e5;background-image:linear-gradient(135deg,#4f46e5,#7c3aed);box-shadow:0 6px 16px rgba(79,70,229,.35);">
                            <a href="{{ $url }}" style="display:inline-block;padding:14px 40px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:12px;letter-spacing:.3px;">
                                @if ($isPartner && $to === \App\Enums\BookingStatus::PendingConfirmation)
                                    Review Booking Sekarang
                                @elseif ($to === \App\Enums\BookingStatus::Approved)
                                    Lakukan Pembayaran
                                @else
                                    Lihat Detail Booking
                                @endif →
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="margin:14px 0 0;font-size:12px;color:#94a3b8;">Tombol tidak berfungsi? Salin tautan ini ke browser:<br>
                    <span style="color:#6366f1;word-break:break-all;">{{ $url }}</span></p>
            </td>
        </tr>

        {{-- ===== INFO PELAKU ===== --}}
        @if ($actor)
        <tr>
            <td align="center" style="padding:14px 32px 30px;">
                <p style="margin:0;font-size:12px;color:#94a3b8;">Perubahan ini dilakukan oleh <b style="color:#475569;">{{ $actor->name }}</b> ({{ $actor->role->label() }}) pada {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB.</p>
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
