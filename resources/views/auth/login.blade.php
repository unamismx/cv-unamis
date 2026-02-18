<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ingreso | CV UNAMIS</title>
  <link rel="stylesheet" href="/css/app.css">
  <style>
    .login-shell {
      min-height: 100vh;
      padding: 28px;
      background:
        radial-gradient(900px 420px at -10% -5%, rgba(66, 133, 244, 0.22), transparent 60%),
        radial-gradient(900px 420px at 110% 105%, rgba(15, 157, 88, 0.16), transparent 56%),
        #f3f7ff;
    }
    .login-wrap {
      width: min(1120px, 96vw);
      margin: 0 auto;
    }
    .login-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #1f2937;
      font-weight: 600;
      letter-spacing: 0.01em;
    }
    .brand img {
      width: 42px;
      height: 42px;
      object-fit: contain;
      border-radius: 10px;
      background: #fff;
      padding: 4px;
      border: 1px solid #d7e3fb;
    }
    .product-chip {
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid #d8e1f4;
      background: #fff;
      color: #35547d;
      font-size: 0.84rem;
    }
    .login-main {
      border-radius: 28px;
      border: 1px solid #dce5f6;
      background: #ffffff;
      box-shadow: 0 24px 56px rgba(31, 57, 104, 0.13);
      padding: 18px;
      display: grid;
      grid-template-columns: 1.3fr 0.9fr;
      gap: 16px;
    }
    .hero {
      border-radius: 22px;
      background: linear-gradient(155deg, #0b57d0 0%, #1a73e8 62%, #74a4f9 100%);
      padding: 34px;
      color: #f8fbff;
      position: relative;
      overflow: hidden;
    }
    .hero::after {
      content: "";
      position: absolute;
      width: 320px;
      height: 320px;
      border-radius: 50%;
      right: -120px;
      bottom: -140px;
      background: rgba(255, 255, 255, 0.14);
    }
    .hero h1 {
      margin: 0 0 10px;
      font-size: 2.05rem;
      line-height: 1.1;
      color: #fff;
      max-width: 520px;
    }
    .hero p {
      margin: 0;
      opacity: 0.95;
      max-width: 520px;
      line-height: 1.5;
    }
    .hero-grid {
      margin-top: 22px;
      display: grid;
      gap: 10px;
      max-width: 560px;
    }
    .hero-item {
      border: 1px solid rgba(255, 255, 255, 0.28);
      background: rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 0.92rem;
    }
    .access-card {
      border-radius: 22px;
      border: 1px solid #dbe4f6;
      background: #f9fbff;
      padding: 28px 22px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 12px;
    }
    .access-title {
      font-size: 1.3rem;
      margin: 0;
      color: #1f2937;
    }
    .access-sub {
      margin: 0 0 4px;
      color: #5f6368;
      font-size: 0.92rem;
    }
    .google-btn {
      width: 100%;
      border: 1px solid #d0dbf3;
      background: #fff;
      color: #1f2937;
      border-radius: 14px;
      padding: 12px 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: 0.18s ease;
    }
    .google-btn:hover {
      border-color: #b8c8e8;
      box-shadow: 0 8px 20px rgba(41, 73, 123, 0.12);
      transform: translateY(-1px);
    }
    .google-mark {
      font-weight: 700;
      color: #ea4335;
      font-size: 1rem;
    }
    .access-note {
      color: #5f6368;
      font-size: 0.84rem;
      margin: 2px 0 0;
    }
    .login-footer {
      margin-top: 14px;
    }
    @media (max-width: 980px) {
      .login-main { grid-template-columns: 1fr; }
      .hero { padding: 24px; }
      .hero h1 { font-size: 1.65rem; }
    }
  </style>
</head>
<body>
  <div class="login-shell">
    <div class="login-wrap">
      <div class="login-topbar">
        <div class="brand">
          <img src="/images/unamis-logo.png" alt="UNAMIS">
          <span>UNAMIS Workspace</span>
        </div>
        <div class="product-chip">Gestor de Curriculum Vitae</div>
      </div>

      <div class="login-main">
        <section class="hero">
          <h1>Acceso al Centro de CV de UNAMIS</h1>
          <p>Completa, actualiza y publica tu CV desde un solo lugar. Cada usuario tiene su propio espacio y el equipo consulta la versión más reciente publicada.</p>
          <div class="hero-grid">
            <div class="hero-item">Personal UNAMIS: entra con tu cuenta institucional de Google.</div>
            <div class="hero-item">Colaboradores externos: entra con tu cuenta Gmail.</div>
            <div class="hero-item">Una sola captura por usuario, con descarga de PDF en español e inglés.</div>
          </div>
        </section>

        <section class="access-card">
          <h2 class="access-title">Iniciar sesión</h2>
          <p class="access-sub">Continúa para entrar a tu panel personal.</p>
          @if ($errors->any())
            <div class="status status-error" style="margin-bottom:4px;">{{ $errors->first() }}</div>
          @endif
          <a class="google-btn" href="/auth/google/redirect">
            <span class="google-mark">G</span>
            <span>Continuar con Google</span>
          </a>
          <p class="access-note">Válido para cuentas Google Workspace y Gmail.</p>
        </section>
      </div>

      <div class="login-footer">
        @include('partials.workspace_footer')
      </div>
    </div>
  </div>
</body>
</html>
