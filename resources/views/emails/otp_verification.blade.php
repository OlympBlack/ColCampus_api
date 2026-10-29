<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation de votre compte - ColCampus</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #F4F2EF; font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="padding: 2.5rem 1rem;">
        {{-- Wrapper principal --}}
        <div
            style="max-width: 520px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #E0DDD8; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">

            <div style="vertical-align: middle;">
                <img src="{{ $message->embed(public_path('images/logo-colcampus.png')) }}" alt="COLCAMPUS"
                    height="200" style="display: block; border: 0;">
            </div>

            {{-- Corps de l'email --}}
            <div style="padding: 3rem 2.5rem 2rem;">
                <h1
                    style="font-size: 24px; font-weight: 600; color: #0F0F0F; margin: 0 0 0.75rem; line-height: 1.2; text-align: center;">
                    Bienvenue sur COLCAMPUS !
                </h1>
                <p style="font-size: 16px; color: #6B6864; margin: 0 0 2.5rem; line-height: 1.6;">
                    Merci de rejoindre notre plateforme. Pour finaliser la création de votre compte, utilisez le code de
                    vérification ci-dessous.
                </p>

                {{-- Bloc OTP --}}
                <div
                    style="background: #F7F3EE; border: 1px solid #E8DFD2; border-radius: 14px; padding: 2rem 1.5rem; text-align: center; margin-bottom: 2rem;">
                    <div
                        style="font-size: 12px; font-weight: 600; letter-spacing: 0.12em; color: #9E9892; text-transform: uppercase; margin-bottom: 1rem;">
                        Code de vérification
                    </div>
                    <div
                        style="font-family: 'Courier New', Courier, monospace; font-size: 42px; font-weight: 700; color: #0F0F0F; letter-spacing: 0.2em;">
                        {{ $otp }}
                    </div>
                </div>

                <p style="font-size: 14px; color: #9E9892; margin: 0 0 2rem; text-align: center;">
                    ⏱ Ce code expire dans <strong>24 heures</strong>.
                </p>

                {{-- Note de sécurité --}}
                <div
                    style="background: #FAFAF9; border-left: 4px solid #E0DDD8; padding: 1.25rem; font-size: 13.5px; color: #8A8882; line-height: 1.6; border-radius: 0 10px 10px 0;">
                    Vous n'avez pas créé de compte sur COLCAMPUS ? Ignorez simplement cet e-mail — aucune action n'est
                    requise.
                </div>
            </div>

            {{-- Pied de page interne --}}
            <div style="border-top: 1px solid #F0EDE9; padding: 1.75rem 2.5rem; background: #fafafa;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="font-size: 14px; color: #6B6864;">
                            L'équipe <strong>{{ config('app.name') }}</strong>
                        </td>
                        <td style="text-align: right;">
                            <a href="#"
                                style="font-size: 13px; color: #B0ADA8; text-decoration: none;">Confidentialité</a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Copyright externe --}}
        <div style="text-align: center; padding: 2rem;">
            <p style="font-size: 11px; color: #B0ADA8; margin: 0; letter-spacing: 0.05em;">
                © {{ date('Y') }} {{ config('app.name') }}. TOUS DROITS RÉSERVÉS.
            </p>
        </div>
    </div>
</body>

</html>