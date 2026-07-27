<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Inter', Arial, sans-serif; background: #f9fafb; margin: 0; padding: 30px 0;">
<div style="max-width: 520px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

    <!-- Header -->
    <div style="background: #E63B2E; padding: 32px 40px; text-align: center;">
        <h1 style="color: white; font-size: 22px; font-weight: 800; margin: 0;">AfricSales</h1>
        <p style="color: rgba(255,255,255,0.8); font-size: 13px; margin: 6px 0 0;">La marketplace africaine de référence</p>
    </div>

    <!-- Body -->
    <div style="padding: 40px;">
        <h2 style="font-size: 18px; font-weight: 700; color: #1f2937; margin: 0 0 8px;">Réinitialisation du mot de passe</h2>
        <p style="font-size: 14px; color: #6b7280; margin: 0 0 24px; line-height: 1.6;">
            Bonjour <strong style="color: #1f2937;">{{ $user->name }}</strong>, voici votre code pour réinitialiser votre mot de passe AfricSales :
        </p>

        <!-- Code -->
        <div style="background: #f9fafb; border: 2px dashed #e5e7eb; border-radius: 16px; padding: 24px; text-align: center; margin: 0 0 24px;">
            <span style="font-size: 42px; font-weight: 800; color: #E63B2E; letter-spacing: 12px;">{{ $code }}</span>
        </div>

        <p style="font-size: 13px; color: #9ca3af; margin: 0 0 24px; text-align: center;">
            ⏰ Ce code expire dans <strong style="color: #6b7280;">10 minutes</strong>
        </p>

        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin: 0 0 24px;">
            <p style="font-size: 12px; color: #92400e; margin: 0;">
                🔒 Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email. Votre mot de passe restera inchangé.
            </p>
        </div>

        <a href="{{ config('app.url') }}/reinitialiser-mot-de-passe"
           style="display: block; background: #E63B2E; color: white; text-decoration: none; text-align: center; padding: 14px 24px; border-radius: 12px; font-weight: 700; font-size: 14px;">
            Réinitialiser mon mot de passe →
        </a>
    </div>

    <!-- Footer -->
    <div style="background: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #f3f4f6;">
        <p style="font-size: 12px; color: #9ca3af; margin: 0;">
            © {{ date('Y') }} AfricSales · contact@africsales.com
        </p>
    </div>
</div>
</body>
</html>
