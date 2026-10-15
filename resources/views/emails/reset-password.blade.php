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
                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#0F172A;">Bonjour {{ $userName }},</p>
                        <p style="margin:0; font-size:15px; line-height:1.6; color:#0F172A;">
                            Vous avez demandé la réinitialisation de votre mot de passe sur <strong>{{ $schoolName }}</strong>.
                            Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
                        </p>
                    </td>
                </tr>

                {{-- Bouton --}}
                <tr>
                    <td style="padding:28px 32px;" align="center">
                        <a href="{{ $resetUrl }}"
                           style="display:inline-block; background-color:#10B981; color:#FFFFFF; text-decoration:none;
                                  font-size:15px; font-weight:600; padding:12px 28px; border-radius:8px;">
                            Réinitialiser mon mot de passe
                        </a>
                    </td>
                </tr>

                {{-- Note expiration --}}
                <tr>
                    <td style="padding:0 32px 28px;">
                        <p style="margin:0; font-size:13px; line-height:1.6; color:#64748B;">
                            Ce lien expire dans 60 minutes. Si vous n'êtes pas à l'origine de cette demande,
                            vous pouvez ignorer cet email sans risque — votre mot de passe actuel reste inchangé.
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
