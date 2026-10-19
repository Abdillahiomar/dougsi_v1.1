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

                {{-- Message --}}
                <tr>
                    <td style="padding:32px 32px 8px;">
                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#0F172A;">Bonjour,</p>
                        <p style="margin:0; font-size:15px; line-height:1.6; color:#0F172A;">
                            Votre facture d'abonnement Dugsi
                            @if ($periodLabel) ({{ $periodLabel }}) @endif
                            est disponible.
                        </p>
                    </td>
                </tr>

                {{-- Détails --}}
                <tr>
                    <td style="padding:20px 32px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background-color:#F1F5F9; border-radius:8px;">
                            <tr>
                                <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; width:140px; vertical-align:top;">N° Facture</td>
                                <td style="padding:12px 16px; font-size:14px; color:#0F172A;" class="mono">{{ $invoiceNumber }}</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; vertical-align:top; border-top:1px solid #E2E8F0;">Montant</td>
                                <td style="padding:12px 16px; font-size:14px; color:#0F172A; border-top:1px solid #E2E8F0;">{{ number_format($amount, 0, ',', ' ') }} FDJ</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; vertical-align:top; border-top:1px solid #E2E8F0;">Émise le</td>
                                <td style="padding:12px 16px; font-size:14px; color:#0F172A; border-top:1px solid #E2E8F0;">{{ $issuedAt }}</td>
                            </tr>
                            @if ($dueAt)
                                <tr>
                                    <td style="padding:12px 16px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#64748B; vertical-align:top; border-top:1px solid #E2E8F0;">Échéance</td>
                                    <td style="padding:12px 16px; font-size:14px; color:#0F172A; border-top:1px solid #E2E8F0;">{{ $dueAt }}</td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 32px 28px;">
                        <p style="margin:0; font-size:13px; line-height:1.6; color:#64748B;">
                            Merci de procéder au règlement avant l'échéance. Pour toute question, contactez l'équipe Dugsi.
                        </p>
                    </td>
                </tr>

                @include('emails.partials.footer')

            </table>
        </td>
    </tr>
</table>
</body>
</html>
