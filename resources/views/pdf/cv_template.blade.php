<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 98px 42px 86px 42px; }

    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
    .fixed-header {
      position: fixed;
      top: -72px;
      left: 0;
      right: 0;
      height: 62px;
      border-bottom: 1px solid #0b4a8b;
      color: #0b3a53;
    }
    .fixed-footer {
      position: fixed;
      bottom: -66px;
      left: 0;
      right: 0;
      height: 58px;
      border-top: 1px solid #cdddf0;
      color: #475569;
      font-size: 8.5px;
    }

    .header-grid, .footer-grid { width: 100%; border-collapse: collapse; }
    .header-grid td, .footer-grid td { border: 0; padding: 0; vertical-align: middle; }
    .brand { color: #0b4a8b; font-size: 8px; letter-spacing: 0.12em; text-transform: uppercase; }
    .header-name { font-size: 12px; font-weight: 700; color: #0b3a53; margin-top: 2px; }
    .doc-id { text-align: right; font-size: 8px; color: #64748b; }
    .doc-id .mono { font-family: DejaVu Sans Mono, monospace; }
    .logo-wrap { width: 130px; }
    .logo-mark {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: #0b4a8b;
      color: #ffffff;
      text-align: center;
      line-height: 34px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.04em;
    }
    .logo-img { width: 118px; height: auto; }
    .footer-main { font-weight: 700; color: #334155; font-size: 8.2px; }
    .footer-line { font-size: 7.9px; color: #64748b; margin-top: 2px; }
    .footer-icon { font-weight: 700; color: #0b4a8b; margin-right: 3px; }
    .footer-right { text-align: right; }
    .footer-signature {
      display: inline-block;
      height: 28px;
      max-width: 120px;
      width: auto;
      margin-top: 2px;
      margin-bottom: 1px;
    }

    .content { border: 1px solid #d9e2ec; border-radius: 10px; padding: 14px; background: #fff; }
    .section { margin: 8px 0 11px; }
    .section-title { background: #edf4fc; border-left: 4px solid #0b4a8b; padding: 5px 8px; font-weight: 700; color: #0b3a53; }
    .label { font-weight: 700; color: #334155; font-size: 8.7px; text-transform: uppercase; letter-spacing: 0.04em; }
    .value { margin-top: 2px; white-space: pre-line; }
    .pro-grid { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .pro-grid td { border: 1px solid #e3edf5; padding: 6px 8px; width: 20%; }

    .meta-grid { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .meta-grid td { border: 1px solid #e3edf5; padding: 6px 8px; width: 33.33%; }

    table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.data th, table.data td { border: 1px solid #d8e2eb; padding: 5px 6px; vertical-align: top; }
    table.data th { background: #eef5fc; font-size: 8.7px; color: #334155; text-align: left; }
    table.data tbody tr:nth-child(even) td { background: #f9fcff; }

    .seal-box { border: 1px solid #d6e2f0; background: #f7fbff; padding: 8px; border-radius: 6px; margin-top: 10px; }
    .seal-grid { width: 100%; border-collapse: collapse; }
    .seal-grid td { border: 0; vertical-align: top; }
    .seal-left { width: 80%; padding-right: 8px; }
    .seal-right { width: 20%; text-align: right; }
    .seal-title { font-size: 8.5px; font-weight: 700; color: #0b4a8b; text-transform: uppercase; letter-spacing: 0.05em; }
    .seal-line { font-size: 8.5px; margin-top: 1px; }
    .mono { font-family: DejaVu Sans Mono, monospace; font-size: 7.6px; word-break: break-all; color: #475569; }
  </style>
</head>
<body>
  @php
    $rawFullName = trim((string) ($loc->title_name ?? ''));
    $titleCandidates = ['Dr.', 'Dra.', 'Enf.', 'Lic.', 'Ing.', 'Mtro.', 'Mtra.', 'QFB', 'Q.F.B.'];
    $professionalTitle = '-';
    $professionalName = $rawFullName ?: '-';
    foreach ($titleCandidates as $candidate) {
        if (str_starts_with($rawFullName, $candidate . ' ')) {
            $professionalTitle = $candidate;
            $professionalName = trim(substr($rawFullName, strlen($candidate)));
            break;
        }
    }

    $formatIndustryDate = function ($value) use ($locale) {
        if (! $value) {
            return '-';
        }
        try {
            $dt = \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return '-';
        }

        $monthsEs = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'];
        $monthsEn = [1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'aug', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dec'];
        $mmm = $locale === 'es' ? $monthsEs[(int) $dt->format('n')] : $monthsEn[(int) $dt->format('n')];

        return $dt->format('d') . '/' . $mmm . '/' . $dt->format('Y');
    };

    $translateToEnglish = function ($value) {
        $text = trim((string) $value);
        if ($text === '' || $text === '-') {
            return '-';
        }

        $exact = config('cv_glossary.exact', []);
        $phrases = config('cv_glossary.phrases', []);

        $normalized = mb_strtolower($text);
        if (isset($exact[$normalized])) {
            return $exact[$normalized];
        }

        // Longest phrases first to avoid partial replacements overriding specific ones.
        uksort($phrases, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $translated = $text;
        foreach ($phrases as $source => $target) {
            $translated = str_ireplace($source, $target, $translated);
        }

        $translated = preg_replace('/\s+/', ' ', trim((string) $translated)) ?? trim((string) $translated);
        return $translated !== '' ? $translated : '-';
    };

    $renderValue = function ($value, bool $translate = false) use ($locale, $translateToEnglish) {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '-';
        }

        if ($locale === 'en' && $translate) {
            return $translateToEnglish($text);
        }

        return $text;
    };

    $renderYearValue = function ($value) {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '-';
        }
        if (preg_match('/^\d{4}$/', $text)) {
            return $text;
        }
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $text, $m)) {
            return $m[1];
        }
        return $text;
    };

    $signatureImagePath = $signatureDataUri ?? null;
  @endphp

  <div class="fixed-header">
    <table class="header-grid">
      <tr>
        <td class="logo-wrap">
          @php
            $logoPath = public_path('images/unamis-logo.png');
          @endphp
          @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="UNAMIS" class="logo-img">
          @else
            <div class="logo-mark">U</div>
          @endif
        </td>
        <td>
          <div class="brand">UNAMIS Curriculum Vitae</div>
          <div class="header-name">Unidad de Atención Médica e Investigación en Salud</div>
        </td>
        <td class="doc-id">
          <div>Folio: <span class="mono">{{ $seal['folio'] }}</span></div>
          <div>ID: <span class="mono">{{ substr($seal['hash'], 0, 12) }}</span></div>
        </td>
      </tr>
    </table>
  </div>

  <div class="fixed-footer">
    <table class="footer-grid">
      <tr>
        <td>
          <div class="footer-main">Unidad de Atención Médica e Investigación en Salud</div>
          <div class="footer-line"><span class="footer-icon">⌂</span>Calle 6 No. 489 A entre calles 17 y 19. Colonia García Ginerés. CP. 97070 Mérida, Yucatán, México.</div>
          <div class="footer-line"><span class="footer-icon">☎</span>+52 999 211 0094</div>
          <div class="footer-line"><span class="footer-icon">✉</span>admin@unamis.com.mx</div>
        </td>
        <td class="footer-right">
          <div class="footer-line">Folio: {{ $seal['folio'] }}</div>
          <div class="footer-line">{{ $labels['date'] }}: {{ now()->format('Y-m-d') }}</div>
          @if($signatureImagePath)
            <div><img class="footer-signature" src="{{ $signatureImagePath }}" alt="Firma"></div>
          @endif
          <div class="footer-line">{{ $labels['signature'] }}: __________________</div>
          <div class="footer-line" id="footer-page-slot"></div>
        </td>
      </tr>
    </table>
  </div>

  <div class="content">
    <div class="section">
      <div class="section-title">{{ $labels['institution'] }}</div>
      <div class="value"><strong>Unidad de Atención Médica e Investigación en Salud</strong></div>
      <div class="value">{{ $institutionalAddress }}</div>
    </div>

    <table class="meta-grid">
      <tr>
        <td>
          <div class="label">{{ $labels['office_phone'] }}</div>
          <div class="value">{{ $loc->office_phone ?: '-' }}</div>
        </td>
        <td>
          <div class="label">{{ $labels['fax'] }}</div>
          <div class="value">{{ $loc->fax_number ?: '-' }}</div>
        </td>
        <td>
          <div class="label">{{ $labels['email'] }}</div>
          <div class="value">{{ $loc->email ?: '-' }}</div>
        </td>
      </tr>
    </table>

    <div class="section">
      <div class="section-title">{{ $locale === 'es' ? 'DATOS DEL PROFESIONAL' : 'PROFESSIONAL DATA' }}</div>
      <table class="pro-grid">
        <tr>
          <td>
            <div class="label">{{ $locale === 'es' ? 'TÍTULO' : 'TITLE' }}</div>
            <div class="value">{{ $professionalTitle }}</div>
          </td>
          <td>
            <div class="label">{{ $locale === 'es' ? 'NOMBRE COMPLETO' : 'FULL NAME' }}</div>
            <div class="value">{{ $professionalName }}</div>
          </td>
          <td>
            <div class="label">{{ $locale === 'es' ? 'CORREO' : 'EMAIL' }}</div>
            <div class="value">{{ $loc->email ?: '-' }}</div>
          </td>
          <td>
            <div class="label">{{ $locale === 'es' ? 'TELÉFONO OFICINA' : 'OFFICE PHONE' }}</div>
            <div class="value">{{ $loc->office_phone ?: '-' }}</div>
          </td>
          <td>
            <div class="label">{{ $locale === 'es' ? 'FAX' : 'FAX' }}</div>
            <div class="value">{{ $loc->fax_number ?: '-' }}</div>
          </td>
        </tr>
      </table>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['profession'] }}</div>
      <div class="value">{{ $renderValue($loc->profession_label, true) }}</div>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['position'] }}</div>
      <div class="value">{{ $renderValue($loc->position_label, true) }}</div>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['education'] }}</div>
      <table class="data">
        <thead>
          <tr>
            <th>{{ $labels['edu_institution_col'] }}</th>
            <th>{{ $labels['edu_place_col'] }}</th>
            <th>{{ $labels['edu_year_col'] }}</th>
            <th>{{ $labels['edu_degree_col'] }}</th>
            <th>{{ $labels['edu_license_col'] }}</th>
          </tr>
        </thead>
        <tbody>
          @if($loc->educations->isNotEmpty())
            @foreach($loc->educations as $edu)
              @php
                $institutionRaw = trim((string) ($edu->institution_other ?? ''));
                $institutionName = $institutionRaw ?: '-';
                $institutionPlace = '-';
                if ($institutionRaw !== '') {
                    if (str_contains($institutionRaw, '|')) {
                        $pair = array_pad(explode('|', $institutionRaw, 2), 2, '-');
                        $institutionName = $pair[0];
                        $institutionPlace = $pair[1];
                    } elseif (str_contains($institutionRaw, ',')) {
                        $parts = explode(',', $institutionRaw);
                        $institutionName = trim((string) array_shift($parts));
                        $institutionPlace = trim(implode(',', $parts)) ?: '-';
                    }
                }
              @endphp
              <tr>
                <td>{{ trim($institutionName) ?: '-' }}</td>
                <td>{{ trim($institutionPlace) ?: '-' }}</td>
                <td>{{ $formatIndustryDate($edu->start_date ?: ($edu->start_year ? ($edu->start_year . '-01-01') : null)) }} - {{ $edu->is_ongoing ? ($locale === 'es' ? 'En curso' : 'Ongoing') : $formatIndustryDate($edu->end_date ?: ($edu->end_year ? ($edu->end_year . '-12-31') : null)) }}</td>
                <td>{{ $renderValue($edu->degree_other, true) }}</td>
                <td>{{ !empty($edu->license_not_applicable) ? ($locale === 'es' ? 'No aplica' : 'Not applicable') : $renderValue($edu->license_number) }}</td>
              </tr>
            @endforeach
          @else
            <tr><td colspan="5">-</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['professional'] }}</div>
      <table class="data">
        <thead>
          <tr>
            <th>{{ $labels['pro_institution_col'] }}</th>
            <th>{{ $labels['pro_role_col'] }}</th>
            <th>{{ $labels['pro_year_col'] }}</th>
          </tr>
        </thead>
        <tbody>
          @php
            $professional = $loc->professional_experience_json ?? [];
          @endphp
          @forelse($professional as $row)
            <tr>
              <td>{{ $row['institution'] ?? '-' }}</td>
              <td>{{ $renderValue($row['position'] ?? '-', true) }}</td>
              <td>{{ $formatIndustryDate($row['start_year'] ?? null) }} - {{ !empty($row['is_ongoing']) ? ($locale === 'es' ? 'En curso' : 'Ongoing') : $formatIndustryDate($row['end_year'] ?? null) }}</td>
            </tr>
          @empty
            <tr><td colspan="3">-</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['clinical'] }}</div>
      <table class="data">
        <thead>
          <tr>
            <th>{{ $labels['clinical_year_col'] }}</th>
            <th>{{ $labels['clinical_therapeutic_col'] }}</th>
            <th>{{ $labels['clinical_role_col'] }}</th>
            <th>{{ $labels['clinical_phase_col'] }}</th>
          </tr>
        </thead>
        <tbody>
          @php
            $clinical = $loc->clinical_research_json ?? [];
          @endphp
          @forelse($clinical as $row)
            <tr>
              <td>{{ $renderYearValue($row['start_year'] ?? null) }} - {{ !empty($row['is_ongoing']) ? ($locale === 'es' ? 'En curso' : 'Ongoing') : $renderYearValue($row['end_year'] ?? null) }}</td>
              <td>{{ $renderValue($row['therapeutic_area'] ?? '-', true) }}</td>
              <td>{{ $renderValue($row['role'] ?? '-', true) }}</td>
              <td>{{ $renderValue($row['phase'] ?? '-') }}</td>
            </tr>
          @empty
            <tr><td colspan="4">-</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['training'] }}</div>
      <table class="data">
        <thead>
          <tr>
            <th>{{ $labels['training_course_col'] }}</th>
            <th>{{ $labels['training_place_col'] }}</th>
            <th>{{ $labels['training_completion_col'] }}</th>
          </tr>
        </thead>
        <tbody>
          @php
            $trainings = $loc->trainings_json ?? [];
          @endphp
          @forelse($trainings as $row)
            <tr>
              <td>{{ $renderValue($row['course'] ?? '-', true) }}</td>
              <td>
                @php
                  $modality = $row['modality'] ?? ($row['place'] ?? null);
                  $modalityLabel = '-';
                  if (!empty($modality)) {
                    $modalityKey = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $modality));
                    if (in_array($modalityKey, ['online', 'en linea', 'virtual'], true)) {
                      $modalityLabel = 'Online';
                    } elseif (in_array($modalityKey, ['presencial', 'in person', 'in-person'], true)) {
                      $modalityLabel = $locale === 'es' ? 'Presencial' : 'In person';
                    } else {
                      $modalityLabel = $renderValue($modality, true);
                    }
                  }
                @endphp
                {{ $modalityLabel }}
              </td>
              <td>{{ !empty($row['completion_date']) ? $formatIndustryDate($row['completion_date']) : ($formatIndustryDate($row['start_year'] ?? null) . ' - ' . (!empty($row['is_ongoing']) ? ($locale === 'es' ? 'En curso' : 'Ongoing') : $formatIndustryDate($row['end_year'] ?? null))) }}</td>
            </tr>
          @empty
            <tr><td colspan="3">-</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section">
      <div class="section-title">{{ $labels['gcp'] }}</div>
      <table class="data">
        <thead>
          <tr>
            <th>{{ $labels['gcp_provider_col'] }}</th>
            <th>{{ $labels['gcp_course_col'] }}</th>
            <th>{{ $labels['gcp_version_col'] }}</th>
            <th>{{ $labels['gcp_issued_col'] }}</th>
            <th>{{ $labels['gcp_expires_col'] }}</th>
            <th>{{ $labels['gcp_status_col'] }}</th>
          </tr>
        </thead>
        <tbody>
          @php
            $gcps = $loc->gcpCertifications ?? collect();
          @endphp
          @if($gcps->isNotEmpty())
            @foreach($gcps as $gcp)
              @php
                $status = (string) ($gcp->status ?? 'unknown');
                $statusLabel = $status;
                if ($locale === 'es') {
                    $statusLabel = match ($status) {
                        'valid' => 'Vigente',
                        'expiring_soon' => 'Por vencer',
                        'expired' => 'Vencido',
                        default => 'Sin dato',
                    };
                } else {
                    $statusLabel = match ($status) {
                        'valid' => 'Valid',
                        'expiring_soon' => 'Expiring soon',
                        'expired' => 'Expired',
                        default => 'Unknown',
                    };
                }
              @endphp
              <tr>
                <td>{{ $gcp->provider ?: '-' }}</td>
                <td>{{ $gcp->course_name ?: '-' }}</td>
                <td>{{ $gcp->guideline_version ?: '-' }}</td>
                <td>{{ $formatIndustryDate(optional($gcp->issued_at)->format('Y-m-d')) }}</td>
                <td>{{ !empty($gcp->no_expiration) ? ($locale === 'es' ? 'No disponible' : 'Not available') : $formatIndustryDate(optional($gcp->expires_at)->format('Y-m-d')) }}</td>
                <td>{{ $statusLabel }}</td>
              </tr>
            @endforeach
          @else
            <tr><td colspan="6">-</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="seal-box">
      <table class="seal-grid">
        <tr>
          <td class="seal-left">
            <div class="seal-title">Sello digital institucional</div>
            <div class="seal-line"><strong>Folio:</strong> {{ $seal['folio'] }}</div>
            <div class="seal-line">Firmante: {{ $seal['signer_email'] }}</div>
            <div class="seal-line">Fecha: {{ $seal['signed_at'] }}</div>
            <div class="mono" style="margin-top:4px;">{{ substr($seal['hash'], 0, 36) }}...</div>
            <div class="mono">{{ $seal['verification_url'] }}</div>
          </td>
          <td class="seal-right">
            <img src="{{ $seal['qr_url'] }}" width="84" height="84" alt="QR verificación">
          </td>
        </tr>
      </table>
    </div>
  </div>

  <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont("DejaVu Sans", "normal");
        $pageText = "{{ $labels['page_word'] }} {PAGE_NUM} {{ $labels['page_of'] }} {PAGE_COUNT}";
        $pdf->page_text(454, 820, $pageText, $font, 7, array(100 / 255, 116 / 255, 139 / 255));
    }
  </script>
</body>
</html>
