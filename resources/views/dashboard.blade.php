<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | CV UNAMIS</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>
  <div class="container">
    @include('partials.workspace_header')

    <div class="card">
      <h1>Centro de Comando de CV</h1>
      <p class="muted">Cada usuario debe mantener su curriculum en dos versiones obligatorias: español e inglés.</p>

      @if(session('ok'))
        <div class="status status-ok">{{ session('ok') }}</div>
      @endif

      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">Estado general</div>
          <div class="kpi-value">{{ strtoupper($myCv->status ?? 'draft') }}</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Versión ES</div>
          <div class="kpi-value">{{ $esReady ? 'Completa' : 'Incompleta' }}</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Versión EN</div>
          <div class="kpi-value">{{ $enReady ? 'Completa' : 'Incompleta' }}</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Firma holográfica</div>
          <div class="kpi-value">{{ $signatureReady ? 'Registrada' : 'Pendiente' }}</div>
        </div>
      </div>

      <div class="top-actions" style="margin-top:14px;">
        <a class="btn" href="/cvs/me">Editar mi CV</a>
        <a class="btn-secondary" href="/cvs/published">Ver CV publicados</a>
      </div>

      <div class="card" style="margin-top:14px; background:#fbfdff;">
        <h2>Captura de firma holográfica</h2>
        <p class="muted">Envía un enlace a tu correo y firma desde móvil o tablet con el dedo o stylus. Esa firma se insertará automáticamente en los PDFs.</p>
        @if($signatureReady)
          <div class="status status-ok">Firma registrada {{ $signatureSignedAt ? $signatureSignedAt->format('Y-m-d H:i') : '' }}</div>
        @endif

        <form method="POST" action="/dashboard/signature/send-link" style="margin-top:10px;">
          @csrf
          <div class="form-row">
            <label>Enviar enlace a correo</label>
            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="correo@ejemplo.com">
          </div>
          <button class="btn" type="submit">Enviar enlace de firma</button>
        </form>

        @if(session('signature_link_url'))
          <div class="status" style="margin-top:10px;">
            Enlace generado para {{ session('signature_link_email') }}:
            <br>
            <a href="{{ session('signature_link_url') }}" target="_blank" rel="noopener">{{ session('signature_link_url') }}</a>
          </div>
        @endif

        @if(session('signature_mail_error'))
          <div class="status status-error" style="margin-top:10px;">Error de correo: {{ session('signature_mail_error') }}</div>
        @endif
      </div>
    </div>

    @include('partials.workspace_footer')
  </div>
</body>
</html>
