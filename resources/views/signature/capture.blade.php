<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Captura de Firma | CV UNAMIS</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f5f8fc; color:#0f172a; margin:0; padding:20px; }
    .card { max-width:760px; margin:0 auto; background:#fff; border:1px solid #dbe5f0; border-radius:14px; padding:18px; }
    h1 { margin:0 0 8px; font-size:24px; }
    .muted { color:#64748b; margin:4px 0 12px; }
    .status { border-radius:10px; padding:10px 12px; margin:10px 0; font-size:14px; }
    .ok { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .pad-wrap { border:2px dashed #93c5fd; border-radius:12px; background:#fff; }
    #signature-pad { width:100%; height:280px; touch-action:none; display:block; }
    .actions { display:flex; gap:10px; margin-top:12px; flex-wrap:wrap; }
    button { border:0; border-radius:10px; padding:10px 14px; cursor:pointer; font-weight:600; }
    .btn { background:#0b4a8b; color:#fff; }
    .btn-light { background:#e2e8f0; color:#1f2937; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Captura de Firma</h1>
    <p class="muted">Correo: <strong>{{ $email }}</strong></p>
    <p class="muted">Este enlace expira: <strong>{{ $expiresAt?->format('Y-m-d H:i') }}</strong></p>

    @if(session('ok'))
      <div class="status ok">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
      <div class="status error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="status error">{{ $errors->first() }}</div>
    @endif

    @if($isUsed)
      <div class="status ok">Este enlace ya fue utilizado. Tu firma ya quedó registrada.</div>
    @endif
    @if($isExpired)
      <div class="status error">Este enlace expiró. Solicita uno nuevo desde tu dashboard.</div>
    @endif

    <form method="POST" action="{{ route('signature.capture.store', ['token' => $token]) }}" id="signature-form">
      @csrf
      <input type="hidden" name="signature_data" id="signature-data">

      <div class="pad-wrap">
        <canvas id="signature-pad"></canvas>
      </div>

      <div class="actions">
        <button class="btn-light" type="button" id="clear-btn" @disabled(!$canCapture)>Limpiar</button>
        <button class="btn" type="submit" @disabled(!$canCapture)>Guardar firma</button>
      </div>
    </form>
  </div>

  <script>
    const canvas = document.getElementById('signature-pad');
    const form = document.getElementById('signature-form');
    const output = document.getElementById('signature-data');
    const clearBtn = document.getElementById('clear-btn');
    const ctx = canvas.getContext('2d');
    const canCapture = @json($canCapture);
    let drawing = false;
    let hasStroke = false;

    function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = canvas.getBoundingClientRect();
      canvas.width = Math.floor(rect.width * ratio);
      canvas.height = Math.floor(rect.height * ratio);
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
      ctx.lineJoin = 'round';
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#0f172a';
      ctx.lineWidth = 2.2;
    }

    function pointFromEvent(e) {
      const rect = canvas.getBoundingClientRect();
      return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function startDraw(e) {
      drawing = true;
      const p = pointFromEvent(e);
      ctx.beginPath();
      ctx.moveTo(p.x, p.y);
      hasStroke = true;
    }

    function moveDraw(e) {
      if (!drawing) return;
      const p = pointFromEvent(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
    }

    function endDraw() {
      drawing = false;
      ctx.closePath();
    }

    canvas.addEventListener('pointerdown', (e) => { e.preventDefault(); startDraw(e); });
    canvas.addEventListener('pointermove', (e) => { e.preventDefault(); moveDraw(e); });
    canvas.addEventListener('pointerup', (e) => { e.preventDefault(); endDraw(e); });
    canvas.addEventListener('pointerleave', (e) => { e.preventDefault(); endDraw(e); });

    clearBtn.addEventListener('click', () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hasStroke = false;
    });

    form.addEventListener('submit', (e) => {
      if (!canCapture) {
        e.preventDefault();
        return;
      }
      if (!hasStroke) {
        e.preventDefault();
        alert('Firma primero en el recuadro.');
        return;
      }
      output.value = canvas.toDataURL('image/png');
    });

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
    if (!canCapture) {
      ctx.globalAlpha = 0.2;
      ctx.fillStyle = '#94a3b8';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.globalAlpha = 1;
    }
  </script>
</body>
</html>
