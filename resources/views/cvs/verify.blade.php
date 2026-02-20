<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verificación de sello digital</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>
  <div class="container" style="max-width:900px; margin-top:30px;">
    <div class="card">
      <h1>Verificación de sello digital</h1>
      <p class="muted">Resultado de autenticidad del CV firmado digitalmente.</p>

      @if(empty($found))
        <div class="status status-error" style="margin-bottom:10px;">
          Folio no encontrado en el registro institucional.
        </div>
      @else
      <div class="status {{ $signatureValid ? 'status-ok' : 'status-error' }}" style="margin-bottom:10px;">
        Firma criptográfica: {{ $signatureValid ? 'VÁLIDA' : 'INVÁLIDA' }}
      </div>

      <div class="status {{ $hashMatchesCurrent ? 'status-ok' : 'status-error' }}">
        Integridad contra versión actual: {{ $hashMatchesCurrent ? 'COINCIDE' : 'NO COINCIDE (el contenido pudo cambiar después de firmado)' }}
      </div>
      @endif

      <div class="table-wrap" style="margin-top:14px;">
        <table>
          <tbody>
            <tr><th>Folio</th><td>{{ $data['folio'] ?? '-' }}</td></tr>
            <tr><th>CV ID</th><td>{{ $data['cv'] ?? '-' }}</td></tr>
            <tr><th>Idioma</th><td>{{ isset($data['locale']) ? strtoupper($data['locale']) : '-' }}</td></tr>
            <tr><th>Firmante</th><td>{{ $data['signer'] ?? '-' }}</td></tr>
            <tr><th>Fecha firma</th><td>{{ $data['signed_at'] ?? '-' }}</td></tr>
            <tr><th>Hash</th><td style="word-break:break-all;">{{ $data['hash'] ?? '-' }}</td></tr>
            <tr><th>Firma HMAC</th><td style="word-break:break-all;">{{ $data['sig'] ?? '-' }}</td></tr>
          </tbody>
        </table>
      </div>

      @if(!empty($downloadLinks))
        <div style="margin-top:16px;">
          <h2 style="margin:0 0 8px 0; font-size:1.05rem;">Descargar CV</h2>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            @if(!empty($downloadLinks['es']))
              <a class="btn-secondary" href="{{ $downloadLinks['es'] }}">Descargar PDF ES</a>
            @endif
            @if(!empty($downloadLinks['en']))
              <a class="btn-secondary" href="{{ $downloadLinks['en'] }}">Download PDF EN</a>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</body>
</html>
