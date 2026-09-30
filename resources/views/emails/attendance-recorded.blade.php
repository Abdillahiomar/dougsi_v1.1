<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $schoolName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#F1F5F9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F1F5F9;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:520px; background-color:#FFFFFF; border-radius:12px; overflow:hidden; border:1px solid #E2E8F0;">

                @include('emails.partials.header')

                {{-- Badge de statut --}}
                <tr>
                    <td style="padding:32px 32px 0;">
                        <span style="display:inline-block; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;
                            background-color:{{ $isLate ? '#FDF3E2' : '#FDECEA' }};
                            color:{{ $isLate ? '#8A6010' : '#C0392B' }};">
                            {{ $isLate ? 'Retard' : 'Absence' }}
                        </span>
                    </td>
                </tr>

                {{-- Message --}}
                <tr>
                    <td style="padding:16px 32px 8px;">
                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#0F172A;">Bonjour,</p>
                        <p style="margin:0; font-size:15px; line-height:1.6; color:#0F172A;">
                            Nous vous informons que <strong>{{ $studentName }}</strong> a été
                            marqué{{ $isLate ? '' : '(e)' }} <strong>{{ $isLate ? 'en retard' : 'absent(e)' }}</strong>
                            le <strong>{{ $date }}</strong>.
                        </p>
                    </td>
                </tr>

                {{-- Détails --}}
                <tr>
                    <td style="padding:20px 32px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background-color:#F1F5F9; border-radius:8px;">
                            @if ($className)
                                <tr>
                                    <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; width:130px; vertical-align:top;">Classe</td>
                                    <td style="padding:12px 16px; font-size:14px; color:#0F172A;">{{ $className }}</td>
                                </tr>
                            @endif
                            @if ($sessionLabel)
                                <tr>
                                    <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; width:130px; vertical-align:top; border-top:1px solid #E2E8F0;">Séance</td>
                                    <td style="padding:12px 16px; font-size:14px; color:#0F172A; border-top:1px solid #E2E8F0;">{{ $sessionLabel }}</td>
                                </tr>
                            @endif
                            @if ($isLate && $lateMinutes)
                                <tr>
                                    <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; width:130px; vertical-align:top; border-top:1px solid #E2E8F0;">Durée du retard</td>
                                    <td style="padding:12px 16px; font-size:14px; color:#0F172A; border-top:1px solid #E2E8F0;">{{ $lateMinutes }} minute(s)</td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>

                @include('emails.partials.footer')

            </table>
        </td>
    </tr>
</table>
</body>
</html>
