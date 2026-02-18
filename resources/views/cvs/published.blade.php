<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CV Publicados | CV UNAMIS</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>
  <div class="container">
    @include('partials.workspace_header')

    <div class="card">
      <h1>CV Publicados</h1>
      <p class="muted">Consulta comunitaria de última versión publicada. Este módulo es solo lectura.</p>

      @if(session('ok'))
        <div class="status status-ok" style="margin-bottom:10px;">{{ session('ok') }}</div>
      @endif
      @if(session('error'))
        <div class="status status-error" style="margin-bottom:10px;">{{ session('error') }}</div>
      @endif

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Profesional</th>
              <th>Correo</th>
              <th>Puesto</th>
              <th>Actualizado</th>
              <th>PDF</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cvs as $cv)
              @php
                $es = $cv->localizations->firstWhere('locale', 'es');
                $en = $cv->localizations->firstWhere('locale', 'en') ?: $es;
                $canDelete = auth()->user()?->isAdmin() || (int) auth()->id() === (int) $cv->user_id;
              @endphp
              <tr>
                <td>{{ $es?->title_name ?: $cv->user?->name ?: 'Sin nombre' }}</td>
                <td>{{ $es?->email ?: $cv->user?->email ?: '-' }}</td>
                <td>{{ $es?->position_label ?: '-' }}</td>
                <td>{{ $cv->last_published_at?->format('Y-m-d H:i') ?: $cv->updated_at?->format('Y-m-d H:i') }}</td>
                <td>
                  <div class="top-actions">
                    @if($es)
                      <a class="btn-secondary" href="/cvs/published/{{ $cv->id }}/pdf/es">ES</a>
                    @endif
                    @if($en)
                      <a class="btn-secondary" href="/cvs/published/{{ $cv->id }}/pdf/en">EN</a>
                    @endif
                  </div>
                </td>
                <td>
                  @if($canDelete)
                    <form method="POST" action="/cvs/published/{{ $cv->id }}" onsubmit="return confirm('¿Eliminar este CV publicado? Esta acción no se puede deshacer.');">
                      @csrf
                      @method('DELETE')
                      <button class="btn-danger" type="submit">Eliminar</button>
                    </form>
                  @else
                    <span class="muted">Sin permiso</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6">No hay CV publicados todavía.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div style="margin-top:12px;">
        {{ $cvs->links() }}
      </div>
    </div>

    @include('partials.workspace_footer')
  </div>
</body>
</html>
