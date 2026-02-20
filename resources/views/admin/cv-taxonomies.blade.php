<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catálogos CV | CV UNAMIS</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>
  <div class="container">
    @include('partials.workspace_header')

    <div class="card">
      <h1>Administración de Catálogos CV</h1>
      <p class="muted">Aquí defines las opciones base para profesión, puesto, rol e indicación terapéutica.</p>

      @if(session('ok'))
        <div class="status status-ok" style="margin-bottom:10px;">{{ session('ok') }}</div>
      @endif

      @foreach($taxonomyTypes as $type => $label)
        @php
          $rows = $terms->get($type, collect());
        @endphp

        <div class="card" style="background:#fbfdff;">
          <h2>{{ $label }}</h2>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ES</th>
                  <th>EN</th>
                  <th>Orden</th>
                  <th>Activo</th>
                  <th>Guardar</th>
                  <th>Eliminar</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rows as $row)
                  <tr>
                    <td>
                      <input type="text" name="name_es" value="{{ $row->name_es }}" form="edit-{{ $row->id }}" required>
                    </td>
                    <td>
                      <input type="text" name="name_en" value="{{ $row->name_en }}" form="edit-{{ $row->id }}">
                    </td>
                    <td>
                      <input type="number" min="1" max="9999" name="sort_order" value="{{ $row->sort_order }}" form="edit-{{ $row->id }}">
                    </td>
                    <td>
                      <label style="display:flex;align-items:center;gap:6px;">
                        <input type="checkbox" name="active" value="1" @checked($row->active) form="edit-{{ $row->id }}">
                        Sí
                      </label>
                    </td>
                    <td>
                      <button class="btn-secondary" type="submit" form="edit-{{ $row->id }}">Guardar</button>
                      <form id="edit-{{ $row->id }}" method="POST" action="/admin/cv-taxonomies/{{ $row->id }}" style="display:none;">
                        @csrf
                        @method('PATCH')
                      </form>
                    </td>
                    <td>
                      <form method="POST" action="/admin/cv-taxonomies/{{ $row->id }}" onsubmit="return confirm('¿Eliminar este término?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger" type="submit">Eliminar</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6">Sin registros.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <form method="POST" action="/admin/cv-taxonomies" style="margin-top:10px;display:grid;grid-template-columns:2fr 2fr 100px 100px 140px;gap:8px;align-items:center;">
            @csrf
            <input type="hidden" name="taxonomy_type" value="{{ $type }}">
            <input type="text" name="name_es" placeholder="Nuevo término ES" required>
            <input type="text" name="name_en" placeholder="Termino EN">
            <input type="number" min="1" max="9999" name="sort_order" placeholder="Orden">
            <label style="display:flex;align-items:center;gap:6px;">
              <input type="checkbox" name="active" value="1" checked>
              Activo
            </label>
            <button class="btn" type="submit">Agregar</button>
          </form>
        </div>
      @endforeach
    </div>

    @include('partials.workspace_footer')
  </div>
</body>
</html>
