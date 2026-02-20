<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Repositorio de Documentos | CV UNAMIS</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>
  <div class="container">
    @include('partials.workspace_header')

    @php
      $formatKb = function ($bytes) {
        $kb = (float) $bytes / 1024;
        return number_format($kb, 1) . ' KB';
      };
    @endphp

    <div class="card">
      <h1>Repositorio de documentos comunitario</h1>
      <p class="muted">
        Sube certificados y documentos de respaldo. Todos los usuarios autenticados pueden descargar.
        Solo el dueño del archivo puede eliminarlo.
      </p>

      @if(session('ok'))
        <div class="status status-ok">{{ session('ok') }}</div>
      @endif
      @if($errors->any())
        <div class="status status-error">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="card" style="margin-top:12px; background:#fbfdff;">
        <h2>Subir documentos</h2>
        <form method="POST" action="/cvs/documents" enctype="multipart/form-data">
          @csrf
          <div class="form-row">
            <label>Categoría</label>
            <select name="category" required>
              <option value="">Seleccionar categoría</option>
              @foreach($categoryLabels as $key => $label)
                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-row">
            <label>Título del documento (opcional)</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="Ej. Cédula Especialidad Medicina Interna">
          </div>
          <div class="form-row">
            <label>Archivo(s)</label>
            <input type="file" name="files[]" multiple required accept=".pdf,.jpg,.jpeg,.png,.webp">
            <small class="muted">Máximo {{ $maxFileKb / 1024 }} MB por archivo. Puedes seleccionar varios a la vez.</small>
          </div>
          <button class="btn" type="submit">Subir documento(s)</button>
        </form>
      </div>
    </div>

    @foreach($entries as $entry)
      @php
        $user = $entry['user'];
        $cv = $entry['cv'];
        $docsByCategory = $entry['documents'];
        $isOwner = (int) auth()->id() === (int) $user->id;
      @endphp
      <div class="card">
        <h2 style="margin-bottom:4px;">{{ $user->name }}</h2>
        <p class="muted" style="margin-bottom:12px;">{{ $user->email }}</p>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="width:220px;">Tipo</th>
                <th>Documento</th>
                <th style="width:130px;">Tamaño</th>
                <th style="width:220px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>CV</td>
                <td>{{ $cv ? 'Última versión publicada' : 'No publicado' }}</td>
                <td>-</td>
                <td>
                  @if($cv)
                    <a class="btn-secondary" href="/cvs/published/{{ $cv->id }}/pdf/es">CV ES</a>
                    <a class="btn-secondary" href="/cvs/published/{{ $cv->id }}/pdf/en">CV EN</a>
                  @else
                    <span class="muted">Sin versión publicada</span>
                  @endif
                </td>
              </tr>
              @foreach($categoryLabels as $categoryKey => $categoryLabel)
                @php
                  $docs = $docsByCategory->get($categoryKey, collect());
                @endphp
                @forelse($docs as $doc)
                  <tr>
                    <td>{{ $categoryLabel }}</td>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $formatKb($doc->file_size_bytes) }}</td>
                    <td>
                      <a class="btn-secondary" href="/cvs/documents/{{ $doc->id }}/download">Descargar</a>
                      @if($isOwner)
                        <form method="POST" action="/cvs/documents/{{ $doc->id }}" class="inline" onsubmit="return confirm('¿Eliminar este documento?');">
                          @csrf
                          @method('DELETE')
                          <button class="btn-danger" type="submit">Eliminar</button>
                        </form>
                      @endif
                    </td>
                  </tr>
                @empty
                @endforelse
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endforeach

    @if(count($entries) === 0)
      <div class="card">
        <p class="muted">Aún no hay CV publicados ni documentos cargados.</p>
      </div>
    @endif

    @include('partials.workspace_footer')
  </div>
</body>
</html>

