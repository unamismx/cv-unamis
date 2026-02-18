@php
  $year = now()->format('Y');
  $owner = config('app.copyright_owner', 'Grupo UNAMIS');
  $copyrightText = config('app.copyright_text', 'Todos los derechos reservados.');
  $dayIndex = (int) config('app.release_day_index', 1);
  $dailyCount = (int) config('app.release_daily_count', 1);
  $version = config('app.app_version', $dayIndex . '.' . $dailyCount);
  $lastUpdateRaw = (string) config('app.last_update', now()->format('Y-m-d H:i:s'));
  $lastUpdate = \Illuminate\Support\Carbon::parse($lastUpdateRaw)->format('Y-m-d H:i:s');
@endphp

<footer class="workspace-footer card">
  <div class="ws-footer-left">
    <strong>{{ $owner }}</strong>
    <span>© {{ $year }} {{ $owner }}. {{ $copyrightText }}</span>
  </div>
  <div class="ws-footer-right">
    <span>Sistema: Gestor de Curriculum Vitae UNAMIS</span>
    <span>Versión {{ $version }} (D.C)</span>
    <span>Última actualización: {{ $lastUpdate }}</span>
  </div>
</footer>
