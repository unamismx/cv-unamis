@php
  $user = auth()->user();
  $initials = collect(explode(' ', trim($user->name ?? 'U')))
      ->filter()
      ->take(2)
      ->map(fn($part) => strtoupper(substr($part, 0, 1)))
      ->implode('');
@endphp

<div class="workspace-header card">
  <div class="ws-left">
    <a href="/dashboard" class="ws-brand">UNAMIS CV Workspace</a>
    <nav class="ws-nav">
      <a class="ws-nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">Inicio</a>
      <a class="ws-nav-link {{ request()->is('cvs/me') ? 'active' : '' }}" href="/cvs/me">Mi CV</a>
      <a class="ws-nav-link {{ request()->is('cvs/published*') ? 'active' : '' }}" href="/cvs/published">CV Publicados</a>
      <a class="ws-nav-link {{ request()->is('cvs/documents*') ? 'active' : '' }}" href="/cvs/documents">Repositorio</a>
      @if($user?->canManageCvTaxonomies())
        <a class="ws-nav-link {{ request()->is('admin/cv-taxonomies*') ? 'active' : '' }}" href="/admin/cv-taxonomies">Catálogos CV</a>
      @endif
    </nav>
  </div>

  <div class="ws-right">
    <div class="ws-user-chip" title="{{ $user->email }}">
      <span class="ws-avatar">{{ $initials ?: 'U' }}</span>
      <span class="ws-user-name">{{ $user->name }}</span>
    </div>

    <form method="POST" action="/logout">
      @csrf
      <button class="btn-secondary" type="submit">Cerrar sesión</button>
    </form>
  </div>
</div>
