<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi CV | CV UNAMIS</title>
  <link rel="stylesheet" href="/css/app.css">
  <style>
    #cv-form .edu-row,
    #cv-form .section-row {
      display: flex !important;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 12px;
      align-items: flex-start;
    }
    #cv-form .field-block {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 210px;
      flex: 1 1 210px;
    }
    #cv-form .field-block.wide {
      min-width: 320px;
      flex: 2 1 320px;
    }
    #cv-form .field-block > span {
      font-size: 12px;
      font-weight: 600;
      color: #374151;
    }
    #cv-form .field-block > input,
    #cv-form .field-block > select {
      min-height: 44px;
      padding: 10px 12px;
      box-sizing: border-box;
      width: 100%;
      margin-top: 0;
    }
    #cv-form .field-check {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      min-height: 44px;
      padding-top: 26px;
      white-space: nowrap;
    }
    #cv-form .other-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 8px;
    }
    @media (max-width: 980px) {
      #cv-form .field-block,
      #cv-form .field-block.wide,
      #cv-form .field-check {
        min-width: 100%;
        flex: 1 1 100%;
      }
      #cv-form .field-check {
        padding-top: 4px;
      }
    }
    .phone-row {
      display: grid;
      grid-template-columns: 130px 86px minmax(220px, 1fr);
      gap: 10px;
      align-items: end;
      max-width: 560px;
    }
    .phone-row .phone-country-wrap {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .phone-row .phone-country-wrap > span {
      font-size: 12px;
      font-weight: 600;
      color: #374151;
    }
    .phone-row .phone-prefix {
      min-height: 44px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      background: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #0f172a;
      padding: 0 10px;
    }
    .phone-row .phone-country {
      min-height: 44px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      padding: 10px 12px;
      background: #fff;
      font-size: 13px;
    }
    .phone-row .phone-national {
      min-height: 44px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      padding: 10px 12px;
      font-size: 16px;
      letter-spacing: .2px;
    }
    @media (max-width: 980px) {
      .phone-row {
        grid-template-columns: 1fr;
      }
      .phone-row .phone-prefix {
        justify-content: flex-start;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    @include('partials.workspace_header')

    @php
      $splitInstitutionPlace = function ($value) {
        $raw = trim((string) $value);
        if ($raw === '') return ['', ''];
        if (str_contains($raw, '|')) {
          $parts = array_pad(explode('|', $raw, 2), 2, '');
          return [trim((string) $parts[0]), trim((string) $parts[1])];
        }
        if (str_contains($raw, ',')) {
          $parts = explode(',', $raw);
          $name = trim((string) array_shift($parts));
          $place = trim(implode(',', $parts));
          return [$name, $place];
        }
        return [$raw, ''];
      };

      $esEducations = old('es.educations', $es?->educations?->map(function($e) use ($splitInstitutionPlace) {
        [$institutionName, $place] = $splitInstitutionPlace($e->institution_other);
        return [
          'institution_id' => $e->institution_id,
          'institution_other' => $institutionName,
          'place' => $place,
          'start_date' => $e->start_date?->format('Y-m-d') ?? ($e->start_year ? ($e->start_year . '-01-01') : null),
          'end_date' => $e->end_date?->format('Y-m-d') ?? ($e->end_year ? ($e->end_year . '-12-31') : null),
          'is_ongoing' => $e->is_ongoing,
          'completion_date' => $e->completion_date?->format('Y-m-d') ?? ($e->year_completed ? ($e->year_completed . '-12-31') : null),
          'degree_id' => $e->degree_id,
          'degree_other' => $e->degree_other,
          'license_number' => $e->license_number,
          'license_not_applicable' => (bool) $e->license_not_applicable,
        ];
      })->toArray() ?? []);
      $enEducations = old('en.educations', $en?->educations?->map(function($e) use ($splitInstitutionPlace) {
        [$institutionName, $place] = $splitInstitutionPlace($e->institution_other);
        return [
          'institution_id' => $e->institution_id,
          'institution_other' => $institutionName,
          'place' => $place,
          'start_date' => $e->start_date?->format('Y-m-d') ?? ($e->start_year ? ($e->start_year . '-01-01') : null),
          'end_date' => $e->end_date?->format('Y-m-d') ?? ($e->end_year ? ($e->end_year . '-12-31') : null),
          'is_ongoing' => $e->is_ongoing,
          'completion_date' => $e->completion_date?->format('Y-m-d') ?? ($e->year_completed ? ($e->year_completed . '-12-31') : null),
          'degree_id' => $e->degree_id,
          'degree_other' => $e->degree_other,
          'license_number' => $e->license_number,
          'license_not_applicable' => (bool) $e->license_not_applicable,
        ];
      })->toArray() ?? []);

      $esProfessional = old('es.professional_experience', $es?->professional_experience_json ?? []);
      $enProfessional = old('en.professional_experience', $en?->professional_experience_json ?? []);
      $toYear = function ($value) {
        $v = trim((string) $value);
        if ($v === '') return null;
        if (preg_match('/^\d{4}$/', $v)) return $v;
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $v, $m)) return $m[1];
        return $v;
      };
      $esClinical = old('es.clinical_research', collect($es?->clinical_research_json ?? [])->map(function($r) use ($toYear) {
        $r['start_year'] = $toYear($r['start_year'] ?? null);
        $r['end_year'] = $toYear($r['end_year'] ?? null);
        return $r;
      })->toArray());
      $enClinical = old('en.clinical_research', collect($en?->clinical_research_json ?? [])->map(function($r) use ($toYear) {
        $r['start_year'] = $toYear($r['start_year'] ?? null);
        $r['end_year'] = $toYear($r['end_year'] ?? null);
        return $r;
      })->toArray());
      $esTrainings = old('es.trainings', collect($es?->trainings_json ?? [])->map(function($r) {
        if (empty($r['modality']) && !empty($r['place'])) {
          $r['modality'] = trim((string) $r['place']);
        }
        return $r;
      })->toArray());
      $enTrainings = old('en.trainings', collect($en?->trainings_json ?? [])->map(function($r) {
        if (empty($r['modality']) && !empty($r['place'])) {
          $r['modality'] = trim((string) $r['place']);
        }
        return $r;
      })->toArray());
      $esGcps = old('es.gcps', $es?->gcpCertifications?->map(fn($g) => [
        'provider' => $g->provider,
        'course_name' => $g->course_name,
        'guideline_version' => $g->guideline_version,
        'certificate_language' => $g->certificate_language,
        'issued_at' => $g->issued_at?->format('Y-m-d'),
        'expires_at' => $g->expires_at?->format('Y-m-d'),
        'no_expiration' => (bool) $g->no_expiration,
        'status' => $g->status,
      ])->toArray() ?? []);
      $enGcps = old('en.gcps', $en?->gcpCertifications?->map(fn($g) => [
        'provider' => $g->provider,
        'course_name' => $g->course_name,
        'guideline_version' => $g->guideline_version,
        'certificate_language' => $g->certificate_language,
        'certificate_id' => $g->certificate_id,
        'issued_at' => $g->issued_at?->format('Y-m-d'),
        'expires_at' => $g->expires_at?->format('Y-m-d'),
        'verification_url' => $g->verification_url,
        'notes' => $g->notes,
        'status' => $g->status,
      ])->toArray() ?? []);

      if (count($esEducations) === 0) $esEducations = [[]];
      if (count($enEducations) === 0) $enEducations = [[]];
      if (count($esProfessional) === 0) $esProfessional = [[]];
      if (count($enProfessional) === 0) $enProfessional = [[]];
      if (count($esClinical) === 0) $esClinical = [[]];
      if (count($enClinical) === 0) $enClinical = [[]];
      if (count($esTrainings) === 0) $esTrainings = [[]];
      if (count($enTrainings) === 0) $enTrainings = [[]];
      if (count($esGcps) === 0) $esGcps = [[]];
      if (count($enGcps) === 0) $enGcps = [[]];

      $institutionsJson = [];
      foreach ($institutions as $institution) {
        $institutionsJson[] = [
          'id' => $institution->id,
          'name' => $institution->name,
          'type' => $institution->institution_type,
        ];
      }

      $degreesJson = [];
      foreach ($degrees as $degree) {
        $degreesJson[] = [
          'id' => $degree->id,
          'es' => $degree->name_es,
          'en' => $degree->name_en,
          'type' => $degree->degree_type,
        ];
      }

      $professionOptions = $taxonomyOptions['professions'] ?? [];
      $studyPositionOptions = $taxonomyOptions['study_positions'] ?? [];
      $studyRoleOptions = $taxonomyOptions['study_roles'] ?? [];
      $therapeuticAreaOptions = $taxonomyOptions['therapeutic_areas'] ?? [];
      $educationDegreeOptions = $taxonomyOptions['education_degrees'] ?? [];

      $rawTitleName = trim((string) old('es.title_name', $es->title_name ?? ''));
      $esTitlePrefix = '';
      $esFullName = $rawTitleName;
      $knownTitleOptions = ['Dr.', 'Dra.', 'Enf.', 'Lic.', 'Ing.', 'Mtro.', 'Mtra.', 'QFB', 'Q.F.B.', 'Br.', 'Br'];
      foreach ($knownTitleOptions as $titleOption) {
        if (str_starts_with($rawTitleName, $titleOption . ' ')) {
          $esTitlePrefix = $titleOption;
          $esFullName = trim(substr($rawTitleName, strlen($titleOption)));
          break;
        }
      }
      if ($esTitlePrefix === '' && preg_match('/^(\S{1,12}\.?)\s+(.+)$/u', $rawTitleName, $m)) {
        $candidate = trim((string) $m[1]);
        $rest = trim((string) $m[2]);
        if ($rest !== '' && (str_ends_with($candidate, '.') || in_array($candidate, $knownTitleOptions, true))) {
          $esTitlePrefix = $candidate;
          $esFullName = $rest;
        }
      }
      $esTitlePrefix = old('es.title_prefix', $esTitlePrefix);
      $esFullName = old('es.full_name', $esFullName);
      $esPhoneRaw = trim((string) old('es.office_phone', $es->office_phone ?? ''));
      $esPhoneCountry = strtoupper(trim((string) old('es.office_phone_country', 'MX')));
      if ($esPhoneCountry === '') $esPhoneCountry = 'MX';
      $esPhoneDigits = preg_replace('/\D+/', '', $esPhoneRaw) ?? '';
      if (str_starts_with($esPhoneDigits, '52') && strlen($esPhoneDigits) > 10) {
        $esPhoneDigits = substr($esPhoneDigits, 2);
      }
      if (strlen($esPhoneDigits) > 10) {
        $esPhoneDigits = substr($esPhoneDigits, -10);
      }
      $esPhoneNational = $esPhoneDigits;
      $esFaxRaw = trim((string) old('es.fax_number', $es->fax_number ?? ''));
      $esFaxCountry = strtoupper(trim((string) old('es.fax_number_country', 'MX')));
      if ($esFaxCountry === '') $esFaxCountry = 'MX';
      $esFaxDigits = preg_replace('/\D+/', '', $esFaxRaw) ?? '';
      if (str_starts_with($esFaxDigits, '52') && strlen($esFaxDigits) > 10) {
        $esFaxDigits = substr($esFaxDigits, 2);
      }
      if (strlen($esFaxDigits) > 10) {
        $esFaxDigits = substr($esFaxDigits, -10);
      }
      $esFaxNational = $esFaxDigits;

      $professionEsList = collect($professionOptions)->pluck('es')->filter()->values()->all();
      $selectedProfession = old('es.profession_label', $es->profession_label ?? '');
      $professionIsOther = ($selectedProfession !== '' && !in_array($selectedProfession, $professionEsList, true)) || $selectedProfession === '__other__';
      $professionOtherEs = old('es.profession_other_es', $professionIsOther && $selectedProfession !== '__other__' ? $selectedProfession : '');
      $professionOtherEn = old('es.profession_other_en', $professionIsOther ? (($en?->profession_label ?? '') ?: '') : '');

      $positionEsList = collect($studyPositionOptions)->pluck('es')->filter()->values()->all();
      $selectedPosition = old('es.position_label', $es->position_label ?? '');
      $positionIsOther = ($selectedPosition !== '' && !in_array($selectedPosition, $positionEsList, true)) || $selectedPosition === '__other__';
      $positionOtherEs = old('es.position_other_es', $positionIsOther && $selectedPosition !== '__other__' ? $selectedPosition : '');
      $positionOtherEn = old('es.position_other_en', $positionIsOther ? (($en?->position_label ?? '') ?: '') : '');
    @endphp

    <div class="card">
      <h1>Mi Curriculum Vitae</h1>
      <p class="muted">Domicilio institucional fijo para todos los CV.</p>
      <div class="status" style="margin-bottom:14px;">{{ $institutionalAddress }}</div>
      <div class="status" style="margin-bottom:10px;">
        Esta pantalla siempre abre tu última versión guardada. Solo editas y actualizas sobre esa base.
      </div>
      <div class="status" style="margin-bottom:10px;">
        Captura única en español: al guardar, la versión en inglés se sincroniza automáticamente con la misma información.
      </div>

      @if(session('ok'))
        <div class="status status-ok" style="margin-bottom:10px;">{{ session('ok') }}</div>
      @endif
      @if(session('error'))
        <div class="status status-error" style="margin-bottom:10px;">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="status status-error" style="margin-bottom:10px;">{{ $errors->first() }}</div>
      @endif

      <form id="cv-form" method="POST" action="/cvs/me">
        @csrf
        @method('PUT')

        <div class="card" style="background:#fbfdff;">
          <h2>Versión Español (ES)</h2>
          <div class="form-row">
            <label>Título profesional</label>
            <input type="text" name="es[title_prefix]" value="{{ $esTitlePrefix }}" placeholder="Ej. Dr., Dra., Lic., Ing.">
          </div>
          <div class="form-row"><label>Nombre completo</label><input type="text" name="es[full_name]" value="{{ $esFullName }}"></div>
          <div class="form-row"><label>Correo</label><input type="text" name="es[email]" value="{{ old('es.email', $es->email ?? '') }}"></div>
          <div class="form-row">
            <label>Teléfono oficina</label>
            <input type="hidden" name="es[office_phone_country]" id="es-office-phone-country-hidden" value="{{ $esPhoneCountry }}">
            <div class="phone-row">
              <div class="phone-country-wrap">
                <span>País</span>
                <select id="es-office-country" class="phone-country" data-target-hidden="es-office-phone-country-hidden">
                  <option value="MX">🇲🇽 México</option>
                </select>
              </div>
              <div class="phone-country-wrap">
                <span>Prefijo</span>
                <div class="phone-prefix">+52</div>
              </div>
              <div class="phone-country-wrap">
                <span>Número (10 dígitos)</span>
                <input
                  id="es-office-phone-national"
                  class="phone-national js-mx-phone"
                  name="es[office_phone]"
                  type="text"
                  inputmode="numeric"
                  autocomplete="tel-national"
                  maxlength="13"
                  placeholder="(999) 2110094"
                  value="{{ $esPhoneNational }}"
                >
              </div>
            </div>
          </div>
          <div class="form-row">
            <label>Fax</label>
            <input type="hidden" name="es[fax_number_country]" id="es-fax-country-hidden" value="{{ $esFaxCountry }}">
            <div class="phone-row">
              <div class="phone-country-wrap">
                <span>País</span>
                <select id="es-fax-country" class="phone-country" data-target-hidden="es-fax-country-hidden">
                  <option value="MX">🇲🇽 México</option>
                </select>
              </div>
              <div class="phone-country-wrap">
                <span>Prefijo</span>
                <div class="phone-prefix">+52</div>
              </div>
              <div class="phone-country-wrap">
                <span>Número (10 dígitos)</span>
                <input
                  id="es-fax-national"
                  class="phone-national"
                  name="es[fax_number]"
                  type="text"
                  inputmode="numeric"
                  autocomplete="tel-national"
                  maxlength="13"
                  placeholder="(999) 2110094"
                  value="{{ $esFaxNational }}"
                >
              </div>
            </div>
          </div>
          <div class="form-row">
            <label>Profesión</label>
            <select name="es[profession_label]" class="taxonomy-select" data-other-prefix="profession">
              <option value="">Seleccionar</option>
              @foreach($professionOptions as $option)
                <option value="{{ $option['es'] }}" @selected($selectedProfession === ($option['es'] ?? ''))>{{ $option['es'] ?? '' }}</option>
              @endforeach
              <option value="__other__" @selected($professionIsOther)>Other / Otro</option>
            </select>
            <div id="profession-other-fields" style="{{ $professionIsOther ? '' : 'display:none;' }}">
              <div class="other-grid">
                <label class="field-block">
                  <span>Other (ES)</span>
                  <input type="text" name="es[profession_other_es]" value="{{ $professionOtherEs }}">
                </label>
                <label class="field-block">
                  <span>Other (EN)</span>
                  <input type="text" name="es[profession_other_en]" value="{{ $professionOtherEn }}">
                </label>
              </div>
            </div>
          </div>
          <div class="form-row">
            <label>Puesto</label>
            <select name="es[position_label]" class="taxonomy-select" data-other-prefix="position">
              <option value="">Seleccionar</option>
              @foreach($studyPositionOptions as $option)
                <option value="{{ $option['es'] }}" @selected($selectedPosition === ($option['es'] ?? ''))>{{ $option['es'] ?? '' }}</option>
              @endforeach
              <option value="__other__" @selected($positionIsOther)>Other / Otro</option>
            </select>
            <div id="position-other-fields" style="{{ $positionIsOther ? '' : 'display:none;' }}">
              <div class="other-grid">
                <label class="field-block">
                  <span>Other (ES)</span>
                  <input type="text" name="es[position_other_es]" value="{{ $positionOtherEs }}">
                </label>
                <label class="field-block">
                  <span>Other (EN)</span>
                  <input type="text" name="es[position_other_en]" value="{{ $positionOtherEn }}">
                </label>
              </div>
            </div>
          </div>

          <h3>Educación (ES) - Captura manual</h3>
          <div id="edu-es-wrapper">
            @foreach($esEducations as $idx => $row)
              <div class="edu-row">
                @php
                  $eduRowEn = $enEducations[$idx] ?? [];
                  $selectedEducationDegree = $row['degree_other'] ?? '';
                  $educationDegreeEsList = collect($educationDegreeOptions)->pluck('es')->filter()->values()->all();
                  $educationDegreeIsOther = ($selectedEducationDegree !== '' && !in_array($selectedEducationDegree, $educationDegreeEsList, true)) || $selectedEducationDegree === '__other__';
                  $educationDegreeOtherEs = old("es.educations.$idx.degree_other_es", $educationDegreeIsOther && $selectedEducationDegree !== '__other__' ? $selectedEducationDegree : '');
                  $educationDegreeOtherEn = old("es.educations.$idx.degree_other_en", $educationDegreeIsOther ? (($eduRowEn['degree_other'] ?? '') ?: '') : '');
                @endphp
                <label class="field-block wide">
                  <span>Universidad / Escuela</span>
                  <input type="text" name="es[educations][{{ $idx }}][institution_other]" value="{{ $row['institution_other'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Lugar</span>
                  <input type="text" name="es[educations][{{ $idx }}][place]" value="{{ $row['place'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Fecha de inicio</span>
                  <input type="date" name="es[educations][{{ $idx }}][start_date]" value="{{ $row['start_date'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Fecha de término</span>
                  <input type="date" id="es-educations-{{ $idx }}-end" name="es[educations][{{ $idx }}][end_date]" value="{{ $row['end_date'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                </label>
                <label class="field-check"><input class="ongoing-toggle" data-end-id="es-educations-{{ $idx }}-end" type="checkbox" name="es[educations][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> En curso</label>
                <label class="field-block wide">
                  <span>Grado obtenido</span>
                  <select name="es[educations][{{ $idx }}][degree_other]" class="taxonomy-select" data-other-prefix="education-{{ $idx }}-degree">
                    <option value="">Seleccionar</option>
                    @foreach($educationDegreeOptions as $option)
                      <option value="{{ $option['es'] }}" @selected($selectedEducationDegree === ($option['es'] ?? ''))>{{ $option['es'] ?? '' }}</option>
                    @endforeach
                    <option value="__other__" @selected($educationDegreeIsOther)>Other / Otro</option>
                  </select>
                  <div id="education-{{ $idx }}-degree-other-fields" style="{{ $educationDegreeIsOther ? '' : 'display:none;' }}">
                    <div class="other-grid">
                      <label class="field-block">
                        <span>Other (ES)</span>
                        <input type="text" name="es[educations][{{ $idx }}][degree_other_es]" value="{{ $educationDegreeOtherEs }}">
                      </label>
                      <label class="field-block">
                        <span>Other (EN)</span>
                        <input type="text" name="es[educations][{{ $idx }}][degree_other_en]" value="{{ $educationDegreeOtherEn }}">
                      </label>
                    </div>
                  </div>
                </label>
                <label class="field-block">
                  <span>Cédula o registro</span>
                  <input type="text" id="es-educations-{{ $idx }}-license" name="es[educations][{{ $idx }}][license_number]" value="{{ $row['license_number'] ?? '' }}" @disabled(!empty($row['license_not_applicable']))>
                </label>
                <label class="field-check"><input class="ongoing-toggle" data-end-id="es-educations-{{ $idx }}-license" type="checkbox" name="es[educations][{{ $idx }}][license_not_applicable]" value="1" @checked(!empty($row['license_not_applicable']))> No aplica</label>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addEducationRow('es')">+ Agregar educación ES</button>

          <h3>Experiencia Profesional (ES)</h3>
          <div id="prof-es-wrapper">
            @foreach($esProfessional as $idx => $row)
              @php
                $professionalRowEn = $enProfessional[$idx] ?? [];
                $selectedProfessionalPosition = $row['position'] ?? '';
                $professionalPositionEsList = collect($studyPositionOptions)->pluck('es')->filter()->values()->all();
                $professionalPositionIsOther = ($selectedProfessionalPosition !== '' && !in_array($selectedProfessionalPosition, $professionalPositionEsList, true)) || $selectedProfessionalPosition === '__other__';
                $professionalPositionOtherEs = old("es.professional_experience.$idx.position_other_es", $professionalPositionIsOther && $selectedProfessionalPosition !== '__other__' ? $selectedProfessionalPosition : '');
                $professionalPositionOtherEn = old("es.professional_experience.$idx.position_other_en", $professionalPositionIsOther ? (($professionalRowEn['position'] ?? '') ?: '') : '');
              @endphp
              <div class="section-row section-5">
                <label class="field-block wide">
                  <span>Institución</span>
                  <input type="text" name="es[professional_experience][{{ $idx }}][institution]" value="{{ $row['institution'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Puesto / Cargo</span>
                  <select name="es[professional_experience][{{ $idx }}][position]" class="taxonomy-select" data-other-prefix="professional-{{ $idx }}-position">
                    <option value="">Seleccionar</option>
                    @foreach($studyPositionOptions as $option)
                      <option value="{{ $option['es'] }}" @selected($selectedProfessionalPosition === ($option['es'] ?? ''))>{{ $option['es'] ?? '' }}</option>
                    @endforeach
                    <option value="__other__" @selected($professionalPositionIsOther)>Other / Otro</option>
                  </select>
                  <div id="professional-{{ $idx }}-position-other-fields" style="{{ $professionalPositionIsOther ? '' : 'display:none;' }}">
                    <div class="other-grid">
                      <label class="field-block">
                        <span>Other (ES)</span>
                        <input type="text" name="es[professional_experience][{{ $idx }}][position_other_es]" value="{{ $professionalPositionOtherEs }}">
                      </label>
                      <label class="field-block">
                        <span>Other (EN)</span>
                        <input type="text" name="es[professional_experience][{{ $idx }}][position_other_en]" value="{{ $professionalPositionOtherEn }}">
                      </label>
                    </div>
                  </div>
                </label>
                <label class="field-block">
                  <span>Fecha de inicio</span>
                  <input type="date" name="es[professional_experience][{{ $idx }}][start_year]" value="{{ $row['start_year'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Fecha de término</span>
                  <input type="date" id="es-professional-{{ $idx }}-end" name="es[professional_experience][{{ $idx }}][end_year]" value="{{ $row['end_year'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                </label>
                <label class="field-check"><input class="ongoing-toggle" data-end-id="es-professional-{{ $idx }}-end" type="checkbox" name="es[professional_experience][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> En curso</label>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addProfessionalRow('es')">+ Agregar experiencia ES</button>

          <h3>Investigación Clínica (ES)</h3>
          <div id="clinical-es-wrapper">
            @foreach($esClinical as $idx => $row)
              <div class="section-row section-7">
                <label class="field-block">
                  <span>Año de inicio</span>
                  <input type="number" min="1900" max="2100" step="1" name="es[clinical_research][{{ $idx }}][start_year]" value="{{ $row['start_year'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Año de término</span>
                  <input type="number" min="1900" max="2100" step="1" id="es-clinical-{{ $idx }}-end" name="es[clinical_research][{{ $idx }}][end_year]" value="{{ $row['end_year'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                </label>
                <label class="field-check"><input class="ongoing-toggle" data-end-id="es-clinical-{{ $idx }}-end" type="checkbox" name="es[clinical_research][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> En curso</label>
                <label class="field-block">
                  <span>Área terapéutica</span>
                  @php
                    $rowEn = $enClinical[$idx] ?? [];
                    $selectedTherapeutic = $row['therapeutic_area'] ?? '';
                    $therapeuticEsList = collect($therapeuticAreaOptions)->pluck('es')->filter()->values()->all();
                    $therapeuticIsOther = ($selectedTherapeutic !== '' && !in_array($selectedTherapeutic, $therapeuticEsList, true)) || $selectedTherapeutic === '__other__';
                    $therapeuticOtherEs = old("es.clinical_research.$idx.therapeutic_area_other_es", $therapeuticIsOther && $selectedTherapeutic !== '__other__' ? $selectedTherapeutic : '');
                    $therapeuticOtherEn = old("es.clinical_research.$idx.therapeutic_area_other_en", $therapeuticIsOther ? (($rowEn['therapeutic_area'] ?? '') ?: '') : '');
                  @endphp
                  <select name="es[clinical_research][{{ $idx }}][therapeutic_area]" class="taxonomy-select" data-other-prefix="clinical-{{ $idx }}-therapeutic">
                    <option value="">Seleccionar</option>
                    @foreach($therapeuticAreaOptions as $option)
                      <option value="{{ $option['es'] }}" @selected($selectedTherapeutic === ($option['es'] ?? ''))>{{ $option['es'] ?? '' }}</option>
                    @endforeach
                    <option value="__other__" @selected($therapeuticIsOther)>Other / Otro</option>
                  </select>
                  <div id="clinical-{{ $idx }}-therapeutic-other-fields" style="{{ $therapeuticIsOther ? '' : 'display:none;' }}">
                    <div class="other-grid">
                      <label class="field-block">
                        <span>Other (ES)</span>
                        <input type="text" name="es[clinical_research][{{ $idx }}][therapeutic_area_other_es]" value="{{ $therapeuticOtherEs }}">
                      </label>
                      <label class="field-block">
                        <span>Other (EN)</span>
                        <input type="text" name="es[clinical_research][{{ $idx }}][therapeutic_area_other_en]" value="{{ $therapeuticOtherEn }}">
                      </label>
                    </div>
                  </div>
                </label>
                <label class="field-block">
                  <span>Rol / Cargo</span>
                  @php
                    $selectedRole = $row['role'] ?? '';
                    $roleEsList = collect($studyRoleOptions)->pluck('es')->filter()->values()->all();
                    $roleIsOther = ($selectedRole !== '' && !in_array($selectedRole, $roleEsList, true)) || $selectedRole === '__other__';
                    $roleOtherEs = old("es.clinical_research.$idx.role_other_es", $roleIsOther && $selectedRole !== '__other__' ? $selectedRole : '');
                    $roleOtherEn = old("es.clinical_research.$idx.role_other_en", $roleIsOther ? (($rowEn['role'] ?? '') ?: '') : '');
                  @endphp
                  <select name="es[clinical_research][{{ $idx }}][role]" class="taxonomy-select" data-other-prefix="clinical-{{ $idx }}-role">
                    <option value="">Seleccionar</option>
                    @foreach($studyRoleOptions as $option)
                      <option value="{{ $option['es'] }}" @selected($selectedRole === ($option['es'] ?? ''))>{{ $option['es'] ?? '' }}</option>
                    @endforeach
                    <option value="__other__" @selected($roleIsOther)>Other / Otro</option>
                  </select>
                  <div id="clinical-{{ $idx }}-role-other-fields" style="{{ $roleIsOther ? '' : 'display:none;' }}">
                    <div class="other-grid">
                      <label class="field-block">
                        <span>Other (ES)</span>
                        <input type="text" name="es[clinical_research][{{ $idx }}][role_other_es]" value="{{ $roleOtherEs }}">
                      </label>
                      <label class="field-block">
                        <span>Other (EN)</span>
                        <input type="text" name="es[clinical_research][{{ $idx }}][role_other_en]" value="{{ $roleOtherEn }}">
                      </label>
                    </div>
                  </div>
                </label>
                <label class="field-block">
                  <span>Fase</span>
                  <input type="text" name="es[clinical_research][{{ $idx }}][phase]" value="{{ $row['phase'] ?? '' }}">
                </label>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addClinicalRow('es')">+ Agregar investigación ES</button>

          <h3>Entrenamientos (ES)</h3>
          <div id="training-es-wrapper">
            @foreach($esTrainings as $idx => $row)
              <div class="section-row section-5">
                <label class="field-block wide">
                  <span>Curso / Entrenamiento</span>
                  <input type="text" name="es[trainings][{{ $idx }}][course]" value="{{ $row['course'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Modalidad</span>
                  <select name="es[trainings][{{ $idx }}][modality]">
                    <option value="">Seleccionar</option>
                    <option value="online" @selected(($row['modality'] ?? '') === 'online')>Online</option>
                    <option value="presencial" @selected(($row['modality'] ?? '') === 'presencial')>Presencial</option>
                  </select>
                </label>
                <label class="field-block">
                  <span>Fecha de inicio</span>
                  <input type="date" name="es[trainings][{{ $idx }}][start_year]" value="{{ $row['start_year'] ?? '' }}">
                </label>
                <label class="field-block">
                  <span>Fecha de término</span>
                  <input type="date" id="es-trainings-{{ $idx }}-end" name="es[trainings][{{ $idx }}][end_year]" value="{{ $row['end_year'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                </label>
                <label class="field-check"><input class="ongoing-toggle" data-end-id="es-trainings-{{ $idx }}-end" type="checkbox" name="es[trainings][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> En curso</label>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addTrainingRow('es')">+ Agregar entrenamiento ES</button>

          <h3>Certificaciones GCP (ES)</h3>
          <div id="gcp-es-wrapper">
            @foreach($esGcps as $idx => $row)
              <div class="card" style="padding:12px;margin-bottom:10px;border-style:dashed;">
                <div class="section-row section-4">
                  <label class="field-block">
                    <span>Proveedor</span>
                    <input type="text" name="es[gcps][{{ $idx }}][provider]" value="{{ $row['provider'] ?? '' }}">
                  </label>
                  <label class="field-block">
                    <span>Nombre del curso</span>
                    <input type="text" name="es[gcps][{{ $idx }}][course_name]" value="{{ $row['course_name'] ?? '' }}">
                  </label>
                  <label class="field-block">
                    <span>Versión guía (ICH)</span>
                    <input type="text" name="es[gcps][{{ $idx }}][guideline_version]" value="{{ $row['guideline_version'] ?? '' }}">
                  </label>
                  <label class="field-block">
                    <span>Idioma del certificado</span>
                    <input type="text" name="es[gcps][{{ $idx }}][certificate_language]" value="{{ $row['certificate_language'] ?? '' }}">
                  </label>
                </div>
                <div class="section-row section-4">
                  <label class="field-block">
                    <span>Fecha de expedición</span>
                    <input type="date" name="es[gcps][{{ $idx }}][issued_at]" value="{{ $row['issued_at'] ?? '' }}">
                  </label>
                  <label class="field-block">
                    <span>Fecha de expiración</span>
                    <input type="date" id="es-gcps-{{ $idx }}-expires" name="es[gcps][{{ $idx }}][expires_at]" value="{{ $row['expires_at'] ?? '' }}" @disabled(!empty($row['no_expiration']))>
                  </label>
                  <label class="field-check"><input class="ongoing-toggle" data-end-id="es-gcps-{{ $idx }}-expires" type="checkbox" name="es[gcps][{{ $idx }}][no_expiration]" value="1" @checked(!empty($row['no_expiration']))> Sin fecha de expiración</label>
                </div>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addGcpRow('es')">+ Agregar certificación GCP ES</button>
        </div>

        @if(false)
        <div class="card" style="background:#fbfdff;">
          <h2>English Version (EN)</h2>
          <div class="form-row"><label>Title, Name and Last Name</label><input type="text" name="en[title_name]" value="{{ old('en.title_name', $en->title_name ?? '') }}"></div>
          <div class="form-row"><label>Email</label><input type="text" name="en[email]" value="{{ old('en.email', $en->email ?? '') }}"></div>
          <div class="form-row"><label>Office phone</label><input type="text" name="en[office_phone]" value="{{ old('en.office_phone', $en->office_phone ?? '') }}"></div>
          <div class="form-row"><label>Fax</label><input type="text" name="en[fax_number]" value="{{ old('en.fax_number', $en->fax_number ?? '') }}"></div>
          <div class="form-row"><label>Position</label><input type="text" name="en[position_label]" value="{{ old('en.position_label', $en->position_label ?? '') }}"></div>

          <h3>Education (EN) - Manual capture</h3>
          <div id="edu-en-wrapper">
            @foreach($enEducations as $idx => $row)
              <div class="edu-row">
                <input type="text" name="en[educations][{{ $idx }}][institution_other]" placeholder="Institution" value="{{ $row['institution_other'] ?? '' }}">
                <input type="date" name="en[educations][{{ $idx }}][start_date]" value="{{ $row['start_date'] ?? '' }}">
                <input type="date" name="en[educations][{{ $idx }}][end_date]" value="{{ $row['end_date'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="en[educations][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> Ongoing</label>
                <input type="date" name="en[educations][{{ $idx }}][completion_date]" value="{{ $row['completion_date'] ?? '' }}">
                <input type="text" name="en[educations][{{ $idx }}][degree_other]" placeholder="Degree / Career / Specialty" value="{{ $row['degree_other'] ?? '' }}">
                <input type="text" name="en[educations][{{ $idx }}][license_number]" placeholder="License/Registry" value="{{ $row['license_number'] ?? '' }}">
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addEducationRow('en')">+ Add education EN</button>

          <h3>Professional Experience (EN)</h3>
          <div id="prof-en-wrapper">
            @foreach($enProfessional as $idx => $row)
              <div class="section-row section-5">
                <input type="text" name="en[professional_experience][{{ $idx }}][institution]" placeholder="Institution" value="{{ $row['institution'] ?? '' }}">
                <input type="text" name="en[professional_experience][{{ $idx }}][position]" placeholder="Position" value="{{ $row['position'] ?? '' }}">
                <input type="date" name="en[professional_experience][{{ $idx }}][start_year]" value="{{ $row['start_year'] ?? '' }}">
                <input type="date" name="en[professional_experience][{{ $idx }}][end_year]" value="{{ $row['end_year'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="en[professional_experience][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> Ongoing</label>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addProfessionalRow('en')">+ Add experience EN</button>

          <h3>Clinical Research (EN)</h3>
          <div id="clinical-en-wrapper">
            @foreach($enClinical as $idx => $row)
              <div class="section-row section-7">
                <input type="date" name="en[clinical_research][{{ $idx }}][start_year]" value="{{ $row['start_year'] ?? '' }}">
                <input type="date" name="en[clinical_research][{{ $idx }}][end_year]" value="{{ $row['end_year'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="en[clinical_research][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> Ongoing</label>
                <input type="text" name="en[clinical_research][{{ $idx }}][therapeutic_area]" placeholder="Therapeutic area" value="{{ $row['therapeutic_area'] ?? '' }}">
                <input type="text" name="en[clinical_research][{{ $idx }}][role]" placeholder="Role" value="{{ $row['role'] ?? '' }}">
                <input type="text" name="en[clinical_research][{{ $idx }}][phase]" placeholder="Phase" value="{{ $row['phase'] ?? '' }}">
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addClinicalRow('en')">+ Add research EN</button>

          <h3>Trainings (EN)</h3>
          <div id="training-en-wrapper">
            @foreach($enTrainings as $idx => $row)
              <div class="section-row section-5">
                <input type="text" name="en[trainings][{{ $idx }}][course]" placeholder="Course" value="{{ $row['course'] ?? '' }}">
                <input type="date" name="en[trainings][{{ $idx }}][start_year]" value="{{ $row['start_year'] ?? '' }}">
                <input type="date" name="en[trainings][{{ $idx }}][end_year]" value="{{ $row['end_year'] ?? '' }}" @disabled(!empty($row['is_ongoing']))>
                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="en[trainings][{{ $idx }}][is_ongoing]" value="1" @checked(!empty($row['is_ongoing']))> Ongoing</label>
                <input type="date" name="en[trainings][{{ $idx }}][completion_date]" value="{{ $row['completion_date'] ?? '' }}">
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addTrainingRow('en')">+ Add training EN</button>

          <h3>GCP Certifications (EN)</h3>
          <div id="gcp-en-wrapper">
            @foreach($enGcps as $idx => $row)
              <div class="card" style="padding:12px;margin-bottom:10px;border-style:dashed;">
                <div class="section-row section-4">
                  <input type="text" name="en[gcps][{{ $idx }}][provider]" placeholder="Provider (e.g., CITI / TransCelerate)" value="{{ $row['provider'] ?? '' }}">
                  <input type="text" name="en[gcps][{{ $idx }}][course_name]" placeholder="Course name" value="{{ $row['course_name'] ?? '' }}">
                  <input type="text" name="en[gcps][{{ $idx }}][guideline_version]" placeholder="Version (e.g., ICH E6(R3))" value="{{ $row['guideline_version'] ?? '' }}">
                  <input type="text" name="en[gcps][{{ $idx }}][certificate_language]" placeholder="Certificate language" value="{{ $row['certificate_language'] ?? '' }}">
                </div>
                <div class="section-row section-4">
                  <input type="text" name="en[gcps][{{ $idx }}][certificate_id]" placeholder="Certificate ID / Ref" value="{{ $row['certificate_id'] ?? '' }}">
                  <input type="date" name="en[gcps][{{ $idx }}][issued_at]" value="{{ $row['issued_at'] ?? '' }}">
                  <input type="date" name="en[gcps][{{ $idx }}][expires_at]" value="{{ $row['expires_at'] ?? '' }}">
                </div>
                <div class="section-row section-3">
                  <input type="url" name="en[gcps][{{ $idx }}][verification_url]" placeholder="Verification URL" value="{{ $row['verification_url'] ?? '' }}">
                  <input type="text" name="en[gcps][{{ $idx }}][notes]" placeholder="Notes" value="{{ $row['notes'] ?? '' }}">
                </div>
              </div>
            @endforeach
          </div>
          <button class="btn-text" type="button" onclick="addGcpRow('en')">+ Add GCP certification EN</button>
        </div>
        @endif

        <div class="top-actions"><button class="btn" type="submit">Guardar CV</button></div>
      </form>

      <div class="top-actions" style="margin-top:10px;">
        <a class="btn-secondary" href="/cvs/me/pdf/es">Descargar PDF ES</a>
        <a class="btn-secondary" href="/cvs/me/pdf/en">Descargar PDF EN</a>
      </div>
      <form method="POST" action="/cvs/me/publish" style="margin-top:10px;">@csrf<button class="btn-secondary" type="submit">Publicar (captura única ES)</button></form>
    </div>

    <div class="card">
      <h2>Historial de versiones</h2>
      <p class="muted">Cada guardado crea una versión. Puedes restaurar una versión anterior en cualquier momento.</p>
      @if($versions->isEmpty())
        <p class="muted">Aún no hay versiones guardadas.</p>
      @else
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Versión</th>
                <th>Fecha</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              @foreach($versions as $version)
                <tr>
                  <td>v{{ $version->version_number }}</td>
                  <td>{{ $version->created_at?->format('Y-m-d H:i') }}</td>
                  <td>
                    <form method="POST" action="/cvs/me/versions/{{ $version->id }}/restore" onsubmit="return confirm('¿Restaurar esta versión?');">
                      @csrf
                      <button class="btn-secondary" type="submit">Restaurar</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    @include('partials.workspace_footer')
  </div>

  <script>
    const professionOptions = @json(collect($professionOptions)->pluck('es')->filter()->values()->all());
    const studyPositionOptions = @json(collect($studyPositionOptions)->pluck('es')->filter()->values()->all());
    const studyRoleOptions = @json(collect($studyRoleOptions)->pluck('es')->filter()->values()->all());
    const therapeuticAreaOptions = @json(collect($therapeuticAreaOptions)->pluck('es')->filter()->values()->all());
    const educationDegreeOptions = @json(collect($educationDegreeOptions)->pluck('es')->filter()->values()->all());

    function renderCatalogSelect(name, options, placeholder = 'Seleccionar', includeOther = false, otherPrefix = '') {
      const opts = [`<option value="">${placeholder}</option>`]
        .concat((options || []).map((opt) => `<option value="${opt}">${opt}</option>`));
      if (includeOther) {
        opts.push('<option value="__other__">Other / Otro</option>');
      }
      const otherAttr = otherPrefix ? ` data-other-prefix="${otherPrefix}"` : '';
      return `<select name="${name}" class="taxonomy-select"${otherAttr}>${opts.join('')}</select>`;
    }

    function renderCatalogSelectWithOther(name, options, prefix, placeholder = 'Seleccionar') {
      const selectHtml = renderCatalogSelect(name, options, placeholder, true, prefix);
      return `${selectHtml}
        <div id="${prefix}-other-fields" style="display:none;">
          <div class="other-grid">
            <label class="field-block"><span>Other (ES)</span><input type="text" name="${name.replace(/\]$/, '_other_es]')}"></label>
            <label class="field-block"><span>Other (EN)</span><input type="text" name="${name.replace(/\]$/, '_other_en]')}"></label>
          </div>
        </div>`;
    }

    function countryFlag(iso2) {
      const code = (iso2 || '').toUpperCase();
      if (!/^[A-Z]{2}$/.test(code)) return '';
      try {
        return String.fromCodePoint(...[...code].map((c) => 127397 + c.charCodeAt(0)));
      } catch (e) {
        return '';
      }
    }

    function countryLabel(iso2) {
      const code = (iso2 || '').toUpperCase();
      if (!/^[A-Z]{2}$/.test(code)) return code || '-';
      const flag = countryFlag(code);
      let countryName = '';
      try {
        if (typeof Intl.DisplayNames === 'function') {
          const names = new Intl.DisplayNames(['es'], { type: 'region' });
          const country = names.of(code);
          if (country && country !== code) countryName = country;
        }
      } catch (e) {}
      const suffix = countryName ? `${code} - ${countryName}` : code;
      return flag ? `${flag} ${suffix}` : suffix;
    }

    function buildCountryOptions(select, selectedCode) {
      const current = (selectedCode || 'MX').toUpperCase();
      let regions = [];
      try {
        if (typeof Intl.supportedValuesOf === 'function') {
          regions = Intl.supportedValuesOf('region');
        }
      } catch (e) {
        regions = [];
      }

      const manualFallback = [
        'MX','US','CA','AR','BR','CL','CO','PE','VE','UY','PY','EC','BO','CR','CU','DO','GT','HN','NI','PA','SV',
        'ES','FR','DE','IT','NL','PT','GB','IE','BE','CH','AT','SE','NO','DK','FI','PL','CZ','GR','TR',
        'CN','JP','KR','IN','AU','NZ','ZA','EG','IL','AE','SA'
      ];

      const source = regions.length ? regions : manualFallback;
      const options = source
        .filter((code) => /^[A-Z]{2}$/.test(code))
        .sort((a, b) => a.localeCompare(b, 'es'));

      select.innerHTML = '';
      options.forEach((code) => {
        const opt = document.createElement('option');
        opt.value = code;
        opt.textContent = countryLabel(code);
        if (code === current) opt.selected = true;
        select.appendChild(opt);
      });

      if (![...select.options].some((o) => o.value === current)) {
        const mx = document.createElement('option');
        mx.value = current;
        mx.textContent = countryLabel(current);
        mx.selected = true;
        select.insertBefore(mx, select.firstChild);
      }
    }

    function onlyDigits(value) {
      return (value || '').replace(/\D+/g, '');
    }

    function formatMexNational(digits) {
      const clean = onlyDigits(digits).slice(0, 10);
      if (clean.length <= 3) return clean ? `(${clean}` : '';
      return `(${clean.slice(0, 3)}) ${clean.slice(3)}`;
    }

    function bindMexPhoneField({ inputId, countryId, hiddenCountryId }) {
      const national = document.getElementById(inputId);
      const country = document.getElementById(countryId);
      const hiddenCountry = document.getElementById(hiddenCountryId);
      if (!national || !country || !hiddenCountry) return;

      const selectedCountry = hiddenCountry.value || 'MX';
      buildCountryOptions(country, selectedCountry);

      const sync = () => {
        const countryValue = (country.value || 'MX').toUpperCase();
        hiddenCountry.value = countryValue;

        const digits = onlyDigits(national.value).slice(0, 10);
        national.value = formatMexNational(digits);
        national.value = digits ? formatMexNational(digits) : '';
      };

      const initialDigits = onlyDigits(national.value).replace(/^52/, '').slice(-10);
      national.value = formatMexNational(initialDigits);
      sync();

      national.addEventListener('input', sync);
      country.addEventListener('change', sync);
    }

    function addEducationRow(locale) {
      const wrapper = document.getElementById(`edu-${locale}-wrapper`);
      const idx = wrapper.children.length;
      const institutionLabel = locale === 'es' ? 'Universidad / Escuela' : 'University / School';
      const placeLabel = locale === 'es' ? 'Lugar' : 'Place';
      const startLabel = locale === 'es' ? 'Fecha de inicio' : 'Start date';
      const endLabel = locale === 'es' ? 'Fecha de término' : 'End date';
      const degreeLabel = locale === 'es' ? 'Grado obtenido' : 'Degree obtained';
      const ongoingLabel = locale === 'es' ? 'En curso' : 'Ongoing';
      const licenseLabel = locale === 'es' ? 'Cédula o registro' : 'License/Registry';
      const notApplyLabel = locale === 'es' ? 'No aplica' : 'Not applicable';
      const row = document.createElement('div');
      row.className = 'edu-row';
      const endId = `${locale}-educations-${idx}-end`;
      const licenseId = `${locale}-educations-${idx}-license`;
      const degreeSelect = locale === 'es'
        ? renderCatalogSelectWithOther(`es[educations][${idx}][degree_other]`, educationDegreeOptions, `education-${idx}-degree`, 'Seleccionar')
        : `<input type="text" name="${locale}[educations][${idx}][degree_other]">`;
      row.innerHTML = `
        <label class="field-block wide"><span>${institutionLabel}</span><input type="text" name="${locale}[educations][${idx}][institution_other]"></label>
        <label class="field-block"><span>${placeLabel}</span><input type="text" name="${locale}[educations][${idx}][place]"></label>
        <label class="field-block"><span>${startLabel}</span><input type="date" name="${locale}[educations][${idx}][start_date]"></label>
        <label class="field-block"><span>${endLabel}</span><input type="date" id="${endId}" name="${locale}[educations][${idx}][end_date]"></label>
        <label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="${locale}[educations][${idx}][is_ongoing]" value="1"> ${ongoingLabel}</label>
        <label class="field-block wide"><span>${degreeLabel}</span>${degreeSelect}</label>
        <label class="field-block"><span>${licenseLabel}</span><input type="text" id="${licenseId}" name="${locale}[educations][${idx}][license_number]"></label>
        <label class="field-check"><input class="ongoing-toggle" data-end-id="${licenseId}" type="checkbox" name="${locale}[educations][${idx}][license_not_applicable]" value="1"> ${notApplyLabel}</label>
      `;
      wrapper.appendChild(row);
      bindOngoingToggles(row);
      bindTaxonomyOtherToggles(row);
    }

    function addProfessionalRow(locale) {
      const wrapper = document.getElementById(`prof-${locale}-wrapper`);
      const idx = wrapper.children.length;
      const row = document.createElement('div');
      row.className = 'section-row section-5';
      const endId = `${locale}-professional-${idx}-end`;
      const positionSelectEs = renderCatalogSelectWithOther(`es[professional_experience][${idx}][position]`, studyPositionOptions, `professional-${idx}-position`, 'Seleccionar');
      row.innerHTML = locale === 'es'
        ? `<label class="field-block wide"><span>Institución</span><input type="text" name="es[professional_experience][${idx}][institution]"></label><label class="field-block"><span>Puesto / Cargo</span>${positionSelectEs}</label><label class="field-block"><span>Fecha de inicio</span><input type="date" name="es[professional_experience][${idx}][start_year]"></label><label class="field-block"><span>Fecha de término</span><input type="date" id="${endId}" name="es[professional_experience][${idx}][end_year]"></label><label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="es[professional_experience][${idx}][is_ongoing]" value="1"> En curso</label>`
        : `<label class="field-block wide"><span>Institution</span><input type="text" name="en[professional_experience][${idx}][institution]"></label><label class="field-block"><span>Position</span><input type="text" name="en[professional_experience][${idx}][position]"></label><label class="field-block"><span>Start date</span><input type="date" name="en[professional_experience][${idx}][start_year]"></label><label class="field-block"><span>End date</span><input type="date" id="${endId}" name="en[professional_experience][${idx}][end_year]"></label><label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="en[professional_experience][${idx}][is_ongoing]" value="1"> Ongoing</label>`;
      wrapper.appendChild(row);
      bindOngoingToggles(row);
      bindTaxonomyOtherToggles(row);
    }

    function addClinicalRow(locale) {
      const wrapper = document.getElementById(`clinical-${locale}-wrapper`);
      const idx = wrapper.children.length;
      const row = document.createElement('div');
      row.className = 'section-row section-7';
      const endId = `${locale}-clinical-${idx}-end`;
      const therapeuticSelectEs = renderCatalogSelectWithOther(`es[clinical_research][${idx}][therapeutic_area]`, therapeuticAreaOptions, `clinical-${idx}-therapeutic`, 'Seleccionar');
      const roleSelectEs = renderCatalogSelectWithOther(`es[clinical_research][${idx}][role]`, studyRoleOptions, `clinical-${idx}-role`, 'Seleccionar');
      row.innerHTML = locale === 'es'
        ? `<label class="field-block"><span>Año de inicio</span><input type="number" min="1900" max="2100" step="1" name="es[clinical_research][${idx}][start_year]"></label><label class="field-block"><span>Año de término</span><input type="number" min="1900" max="2100" step="1" id="${endId}" name="es[clinical_research][${idx}][end_year]"></label><label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="es[clinical_research][${idx}][is_ongoing]" value="1"> En curso</label><label class="field-block"><span>Área terapéutica</span>${therapeuticSelectEs}</label><label class="field-block"><span>Rol / Cargo</span>${roleSelectEs}</label><label class="field-block"><span>Fase</span><input type="text" name="es[clinical_research][${idx}][phase]"></label>`
        : `<label class="field-block"><span>Start year</span><input type="number" min="1900" max="2100" step="1" name="en[clinical_research][${idx}][start_year]"></label><label class="field-block"><span>End year</span><input type="number" min="1900" max="2100" step="1" id="${endId}" name="en[clinical_research][${idx}][end_year]"></label><label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="en[clinical_research][${idx}][is_ongoing]" value="1"> Ongoing</label><label class="field-block"><span>Therapeutic area</span><input type="text" name="en[clinical_research][${idx}][therapeutic_area]"></label><label class="field-block"><span>Role</span><input type="text" name="en[clinical_research][${idx}][role]"></label><label class="field-block"><span>Phase</span><input type="text" name="en[clinical_research][${idx}][phase]"></label>`;
      wrapper.appendChild(row);
      bindOngoingToggles(row);
      bindTaxonomyOtherToggles(row);
    }

    function addTrainingRow(locale) {
      const wrapper = document.getElementById(`training-${locale}-wrapper`);
      const idx = wrapper.children.length;
      const row = document.createElement('div');
      row.className = 'section-row section-5';
      const endId = `${locale}-trainings-${idx}-end`;
      const modalityLabel = locale === 'es' ? 'Modalidad' : 'Modality';
      row.innerHTML = locale === 'es'
        ? `<label class="field-block wide"><span>Curso / Entrenamiento</span><input type="text" name="es[trainings][${idx}][course]"></label><label class="field-block"><span>${modalityLabel}</span><select name="es[trainings][${idx}][modality]"><option value="">Seleccionar</option><option value="online">Online</option><option value="presencial">Presencial</option></select></label><label class="field-block"><span>Fecha de inicio</span><input type="date" name="es[trainings][${idx}][start_year]"></label><label class="field-block"><span>Fecha de término</span><input type="date" id="${endId}" name="es[trainings][${idx}][end_year]"></label><label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="es[trainings][${idx}][is_ongoing]" value="1"> En curso</label>`
        : `<label class="field-block wide"><span>Course / Training</span><input type="text" name="en[trainings][${idx}][course]"></label><label class="field-block"><span>${modalityLabel}</span><select name="en[trainings][${idx}][modality]"><option value="">Select</option><option value="online">Online</option><option value="presencial">In person</option></select></label><label class="field-block"><span>Start date</span><input type="date" name="en[trainings][${idx}][start_year]"></label><label class="field-block"><span>End date</span><input type="date" id="${endId}" name="en[trainings][${idx}][end_year]"></label><label class="field-check"><input class="ongoing-toggle" data-end-id="${endId}" type="checkbox" name="en[trainings][${idx}][is_ongoing]" value="1"> Ongoing</label>`;
      wrapper.appendChild(row);
      bindOngoingToggles(row);
    }

    function addGcpRow(locale) {
      const wrapper = document.getElementById(`gcp-${locale}-wrapper`);
      const idx = wrapper.children.length;
      const es = locale === 'es';
      const card = document.createElement('div');
      card.className = 'card';
      card.style.padding = '12px';
      card.style.marginBottom = '10px';
      card.style.borderStyle = 'dashed';
      const expiresId = `${locale}-gcps-${idx}-expires`;
      card.innerHTML = `
        <div class="section-row section-4">
          <label class="field-block"><span>${es ? 'Proveedor' : 'Provider'}</span><input type="text" name="${locale}[gcps][${idx}][provider]"></label>
          <label class="field-block"><span>${es ? 'Nombre del curso' : 'Course name'}</span><input type="text" name="${locale}[gcps][${idx}][course_name]"></label>
          <label class="field-block"><span>${es ? 'Versión guía (ICH)' : 'Guideline version (ICH)'}</span><input type="text" name="${locale}[gcps][${idx}][guideline_version]"></label>
          <label class="field-block"><span>${es ? 'Idioma del certificado' : 'Certificate language'}</span><input type="text" name="${locale}[gcps][${idx}][certificate_language]"></label>
        </div>
        <div class="section-row section-4">
          <label class="field-block"><span>${es ? 'Fecha de expedición' : 'Issue date'}</span><input type="date" name="${locale}[gcps][${idx}][issued_at]"></label>
          <label class="field-block"><span>${es ? 'Fecha de expiración' : 'Expiration date'}</span><input type="date" id="${expiresId}" name="${locale}[gcps][${idx}][expires_at]"></label>
          <label class="field-check"><input class="ongoing-toggle" data-end-id="${expiresId}" type="checkbox" name="${locale}[gcps][${idx}][no_expiration]" value="1"> ${es ? 'Sin fecha de expiración' : 'No expiration date'}</label>
        </div>
      `;
      wrapper.appendChild(card);
      bindOngoingToggles(card);
    }

    function bindOngoingToggles(scope = document) {
      scope.querySelectorAll('.ongoing-toggle').forEach((checkbox) => {
        const targetId = checkbox.dataset.endId;
        if (!targetId) {
          return;
        }
        const target = document.getElementById(targetId);
        if (!target) {
          return;
        }
        const sync = () => {
          if (checkbox.checked) {
            target.value = '';
            target.disabled = true;
          } else {
            target.disabled = false;
          }
        };
        checkbox.addEventListener('change', sync);
        sync();
      });
    }

    function bindTaxonomyOtherToggles(scope = document) {
      scope.querySelectorAll('.taxonomy-select').forEach((select) => {
        const prefix = select.dataset.otherPrefix;
        if (!prefix) return;
        const wrapper = document.getElementById(`${prefix}-other-fields`);
        if (!wrapper) return;
        const sync = () => {
          const visible = select.value === '__other__';
          wrapper.style.display = visible ? '' : 'none';
          wrapper.querySelectorAll('input').forEach((input) => {
            input.disabled = !visible;
            if (!visible) {
              input.value = '';
            }
          });
        };
        select.addEventListener('change', sync);
        sync();
      });
    }

    bindMexPhoneField({
      inputId: 'es-office-phone-national',
      countryId: 'es-office-country',
      hiddenCountryId: 'es-office-phone-country-hidden',
    });
    bindMexPhoneField({
      inputId: 'es-fax-national',
      countryId: 'es-fax-country',
      hiddenCountryId: 'es-fax-country-hidden',
    });
    bindOngoingToggles();
    bindTaxonomyOtherToggles();
  </script>
</body>
</html>
