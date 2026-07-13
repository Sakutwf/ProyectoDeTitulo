<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campana->asunto_correo }}</title>
</head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#172033">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;background:#f4f6f8">
    <tr><td align="center">
        <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:100%;background:#fff;border-radius:16px;overflow:hidden">
            <tr><td style="height:10px;background:#e01e1e"></td></tr>
            <tr><td style="padding:30px">
                <p style="margin:0 0 18px;color:#e01e1e;font-weight:700">Cruz Roja Chilena</p>
                <h1 style="margin:0 0 18px;font-size:24px">{{ $campana->asunto_correo }}</h1>
                @if($recipientName)<p>Hola {{ $recipientName }},</p>@endif
                <div style="font-size:16px;line-height:1.6;white-space:pre-line">{{ $campana->mensaje }}</div>
                @if(data_get($campana->metadatos, 'url'))
                    <p style="margin:26px 0 0">
                        <a href="{{ data_get($campana->metadatos, 'url') }}" style="display:inline-block;background:#e01e1e;color:#fff;text-decoration:none;padding:12px 20px;border-radius:9px;font-weight:700">Ver en el sistema</a>
                    </p>
                @endif
            </td></tr>
            <tr><td style="padding:18px 30px;background:#f8fafc;color:#687386;font-size:12px">Este mensaje fue generado por el sistema de gestión de Cruz Roja.</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
