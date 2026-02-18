<?php

namespace App\Http\Controllers;

use App\Models\CatalogDegree;
use App\Models\CatalogInstitution;
use App\Models\Cv;
use App\Models\CvImportJob;
use App\Models\CvLocalization;
use App\Models\CvDocumentSeal;
use App\Models\CvVersion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use ZipArchive;

class CvController extends Controller
{
    public function edit(): View
    {
        $cv = $this->resolveCv();
        $cv->load(['user:id,signature_file_path', 'localizations.educations', 'localizations.gcpCertifications']);
        $localizations = $cv->localizations->keyBy('locale');

        return view('cvs.edit', [
            'cv' => $cv,
            'es' => $localizations->get('es'),
            'en' => $localizations->get('en'),
            'versions' => $cv->versions()->limit(20)->get(),
            'institutions' => CatalogInstitution::where('active', true)->orderBy('institution_type')->orderBy('name')->get(),
            'degrees' => CatalogDegree::where('active', true)->orderBy('degree_type')->orderBy('name_es')->get(),
            'lastImport' => CvImportJob::where('user_id', auth()->id())->latest()->first(),
            'institutionalAddress' => config('cv.institutional_address'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'es.title_name' => ['nullable', 'string', 'max:220'],
            'es.title_prefix' => ['nullable', 'string', 'max:40'],
            'es.full_name' => ['nullable', 'string', 'max:220'],
            'es.office_phone' => ['nullable', 'string', 'max:50'],
            'es.office_phone_country' => ['nullable', 'string', 'size:2'],
            'es.fax_number' => ['nullable', 'string', 'max:50'],
            'es.fax_number_country' => ['nullable', 'string', 'size:2'],
            'es.email' => ['nullable', 'email', 'max:180'],
            'es.position_label' => ['nullable', 'string', 'max:180'],
            'es.educations' => ['nullable', 'array'],
            'es.educations.*.institution_id' => ['nullable', 'integer', 'exists:catalog_institutions,id'],
            'es.educations.*.institution_other' => ['nullable', 'string', 'max:220'],
            'es.educations.*.place' => ['nullable', 'string', 'max:220'],
            'es.educations.*.start_date' => ['nullable', 'date_format:Y-m-d'],
            'es.educations.*.end_date' => ['nullable', 'date_format:Y-m-d'],
            'es.educations.*.is_ongoing' => ['nullable', 'boolean'],
            'es.educations.*.completion_date' => ['nullable', 'date_format:Y-m-d'],
            'es.educations.*.degree_id' => ['nullable', 'integer', 'exists:catalog_degrees,id'],
            'es.educations.*.degree_other' => ['nullable', 'string', 'max:220'],
            'es.educations.*.license_number' => ['nullable', 'string', 'max:120'],
            'es.educations.*.license_not_applicable' => ['nullable', 'boolean'],
            'es.professional_experience' => ['nullable', 'array'],
            'es.professional_experience.*.institution' => ['nullable', 'string', 'max:220'],
            'es.professional_experience.*.position' => ['nullable', 'string', 'max:220'],
            'es.professional_experience.*.start_year' => ['nullable', 'date_format:Y-m-d'],
            'es.professional_experience.*.end_year' => ['nullable', 'date_format:Y-m-d'],
            'es.professional_experience.*.is_ongoing' => ['nullable', 'boolean'],
            'es.clinical_research' => ['nullable', 'array'],
            'es.clinical_research.*.start_year' => ['nullable', 'digits:4', 'integer', 'between:1900,2100'],
            'es.clinical_research.*.end_year' => ['nullable', 'digits:4', 'integer', 'between:1900,2100'],
            'es.clinical_research.*.is_ongoing' => ['nullable', 'boolean'],
            'es.clinical_research.*.therapeutic_area' => ['nullable', 'string', 'max:220'],
            'es.clinical_research.*.role' => ['nullable', 'string', 'max:180'],
            'es.clinical_research.*.phase' => ['nullable', 'string', 'max:60'],
            'es.trainings' => ['nullable', 'array'],
            'es.trainings.*.course' => ['nullable', 'string', 'max:220'],
            'es.trainings.*.modality' => ['nullable', 'string', 'in:online,presencial'],
            'es.trainings.*.start_year' => ['nullable', 'date_format:Y-m-d'],
            'es.trainings.*.end_year' => ['nullable', 'date_format:Y-m-d'],
            'es.trainings.*.is_ongoing' => ['nullable', 'boolean'],
            'es.trainings.*.completion_date' => ['nullable', 'date_format:Y-m-d'],
            'es.gcps' => ['nullable', 'array'],
            'es.gcps.*.provider' => ['nullable', 'string', 'max:180'],
            'es.gcps.*.course_name' => ['nullable', 'string', 'max:220'],
            'es.gcps.*.guideline_version' => ['nullable', 'string', 'max:60'],
            'es.gcps.*.certificate_language' => ['nullable', 'string', 'max:40'],
            'es.gcps.*.issued_at' => ['nullable', 'date_format:Y-m-d'],
            'es.gcps.*.expires_at' => ['nullable', 'date_format:Y-m-d'],
            'es.gcps.*.no_expiration' => ['nullable', 'boolean'],
            'en.title_name' => ['nullable', 'string', 'max:220'],
            'en.title_prefix' => ['nullable', 'string', 'max:40'],
            'en.full_name' => ['nullable', 'string', 'max:220'],
            'en.office_phone' => ['nullable', 'string', 'max:50'],
            'en.office_phone_country' => ['nullable', 'string', 'size:2'],
            'en.fax_number' => ['nullable', 'string', 'max:50'],
            'en.fax_number_country' => ['nullable', 'string', 'size:2'],
            'en.email' => ['nullable', 'email', 'max:180'],
            'en.position_label' => ['nullable', 'string', 'max:180'],
            'en.educations' => ['nullable', 'array'],
            'en.educations.*.institution_id' => ['nullable', 'integer', 'exists:catalog_institutions,id'],
            'en.educations.*.institution_other' => ['nullable', 'string', 'max:220'],
            'en.educations.*.place' => ['nullable', 'string', 'max:220'],
            'en.educations.*.start_date' => ['nullable', 'date_format:Y-m-d'],
            'en.educations.*.end_date' => ['nullable', 'date_format:Y-m-d'],
            'en.educations.*.is_ongoing' => ['nullable', 'boolean'],
            'en.educations.*.completion_date' => ['nullable', 'date_format:Y-m-d'],
            'en.educations.*.degree_id' => ['nullable', 'integer', 'exists:catalog_degrees,id'],
            'en.educations.*.degree_other' => ['nullable', 'string', 'max:220'],
            'en.educations.*.license_number' => ['nullable', 'string', 'max:120'],
            'en.educations.*.license_not_applicable' => ['nullable', 'boolean'],
            'en.professional_experience' => ['nullable', 'array'],
            'en.professional_experience.*.institution' => ['nullable', 'string', 'max:220'],
            'en.professional_experience.*.position' => ['nullable', 'string', 'max:220'],
            'en.professional_experience.*.start_year' => ['nullable', 'date_format:Y-m-d'],
            'en.professional_experience.*.end_year' => ['nullable', 'date_format:Y-m-d'],
            'en.professional_experience.*.is_ongoing' => ['nullable', 'boolean'],
            'en.clinical_research' => ['nullable', 'array'],
            'en.clinical_research.*.start_year' => ['nullable', 'digits:4', 'integer', 'between:1900,2100'],
            'en.clinical_research.*.end_year' => ['nullable', 'digits:4', 'integer', 'between:1900,2100'],
            'en.clinical_research.*.is_ongoing' => ['nullable', 'boolean'],
            'en.clinical_research.*.therapeutic_area' => ['nullable', 'string', 'max:220'],
            'en.clinical_research.*.role' => ['nullable', 'string', 'max:180'],
            'en.clinical_research.*.phase' => ['nullable', 'string', 'max:60'],
            'en.trainings' => ['nullable', 'array'],
            'en.trainings.*.course' => ['nullable', 'string', 'max:220'],
            'en.trainings.*.modality' => ['nullable', 'string', 'in:online,presencial'],
            'en.trainings.*.start_year' => ['nullable', 'date_format:Y-m-d'],
            'en.trainings.*.end_year' => ['nullable', 'date_format:Y-m-d'],
            'en.trainings.*.is_ongoing' => ['nullable', 'boolean'],
            'en.trainings.*.completion_date' => ['nullable', 'date_format:Y-m-d'],
            'en.gcps' => ['nullable', 'array'],
            'en.gcps.*.provider' => ['nullable', 'string', 'max:180'],
            'en.gcps.*.course_name' => ['nullable', 'string', 'max:220'],
            'en.gcps.*.guideline_version' => ['nullable', 'string', 'max:60'],
            'en.gcps.*.certificate_language' => ['nullable', 'string', 'max:40'],
            'en.gcps.*.issued_at' => ['nullable', 'date_format:Y-m-d'],
            'en.gcps.*.expires_at' => ['nullable', 'date_format:Y-m-d'],
            'en.gcps.*.no_expiration' => ['nullable', 'boolean'],
        ]);

        $cv = $this->resolveCv();
        $payloads = ['es' => $data['es'] ?? [], 'en' => $data['es'] ?? []];

        $rawEsOfficePhone = $payloads['es']['office_phone'] ?? null;
        $rawEnOfficePhone = $payloads['en']['office_phone'] ?? null;
        $rawEsFaxNumber = $payloads['es']['fax_number'] ?? null;
        $rawEnFaxNumber = $payloads['en']['fax_number'] ?? null;
        $normalizedEsOfficePhone = $this->normalizeMexOfficePhone($rawEsOfficePhone);
        $normalizedEnOfficePhone = $this->normalizeMexOfficePhone($rawEnOfficePhone);
        $normalizedEsFaxNumber = $this->normalizeMexOfficePhone($rawEsFaxNumber);
        $normalizedEnFaxNumber = $this->normalizeMexOfficePhone($rawEnFaxNumber);

        if ($rawEsOfficePhone !== null && trim((string) $rawEsOfficePhone) !== '' && $normalizedEsOfficePhone === null) {
            return redirect('/cvs/me')
                ->withInput()
                ->withErrors(['es.office_phone' => 'El teléfono debe tener 10 dígitos en formato +52 (xxx) xxxxxxx.']);
        }

        if ($rawEnOfficePhone !== null && trim((string) $rawEnOfficePhone) !== '' && $normalizedEnOfficePhone === null) {
            return redirect('/cvs/me')
                ->withInput()
                ->withErrors(['en.office_phone' => 'Phone must have 10 digits in +52 (xxx) xxxxxxx format.']);
        }
        if ($rawEsFaxNumber !== null && trim((string) $rawEsFaxNumber) !== '' && $normalizedEsFaxNumber === null) {
            return redirect('/cvs/me')
                ->withInput()
                ->withErrors(['es.fax_number' => 'El fax debe tener 10 dígitos en formato +52 (xxx) xxxxxxx.']);
        }
        if ($rawEnFaxNumber !== null && trim((string) $rawEnFaxNumber) !== '' && $normalizedEnFaxNumber === null) {
            return redirect('/cvs/me')
                ->withInput()
                ->withErrors(['en.fax_number' => 'Fax must have 10 digits in +52 (xxx) xxxxxxx format.']);
        }

        $payloads['es']['office_phone'] = $normalizedEsOfficePhone;
        $payloads['en']['office_phone'] = $normalizedEnOfficePhone;
        $payloads['es']['fax_number'] = $normalizedEsFaxNumber;
        $payloads['en']['fax_number'] = $normalizedEnFaxNumber;

        DB::transaction(function () use ($cv, $payloads) {
            foreach (['es', 'en'] as $locale) {
                $payload = $payloads[$locale] ?? [];
                $titlePrefix = trim((string) ($payload['title_prefix'] ?? ''));
                $fullName = trim((string) ($payload['full_name'] ?? ''));
                $composedTitleName = trim($titlePrefix . ' ' . $fullName);
                $titleName = $composedTitleName !== '' ? $composedTitleName : ($payload['title_name'] ?? null);

                $localization = CvLocalization::updateOrCreate(
                    ['cv_id' => $cv->id, 'locale' => $locale],
                    [
                        'title_name' => $titleName,
                        'office_phone' => $payload['office_phone'] ?? null,
                        'fax_number' => $payload['fax_number'] ?? null,
                        'email' => $payload['email'] ?? null,
                        'position_label' => $payload['position_label'] ?? null,
                        'summary_text' => $payload['summary_text'] ?? null,
                        'professional_experience_json' => $this->normalizeRows(
                            $payload['professional_experience'] ?? [],
                            ['institution', 'position', 'start_year', 'end_year', 'is_ongoing']
                        ),
                        'clinical_research_json' => $this->normalizeRows(
                            $payload['clinical_research'] ?? [],
                            ['start_year', 'end_year', 'is_ongoing', 'therapeutic_area', 'role', 'phase']
                        ),
                        'trainings_json' => $this->normalizeRows(
                            $payload['trainings'] ?? [],
                            ['course', 'modality', 'start_year', 'end_year', 'is_ongoing', 'completion_date']
                        ),
                    ]
                );

                $localization->educations()->delete();
                $rows = $payload['educations'] ?? [];

                foreach ($rows as $index => $row) {
                    $hasData = ! empty($row['institution_id'])
                        || ! empty($row['institution_other'])
                        || ! empty($row['place'])
                        || ! empty($row['start_date'])
                        || ! empty($row['end_date'])
                        || ! empty($row['is_ongoing'])
                        || ! empty($row['completion_date'])
                        || ! empty($row['degree_id'])
                        || ! empty($row['degree_other'])
                        || ! empty($row['license_number'])
                        || ! empty($row['license_not_applicable']);

                    if (! $hasData) {
                        continue;
                    }

                    $startDate = $row['start_date'] ?? null;
                    $endDate = ! empty($row['is_ongoing']) ? null : ($row['end_date'] ?? null);
                    $completionDate = $row['completion_date'] ?? null;

                    $place = trim((string) ($row['place'] ?? ''));
                    $institutionOther = trim((string) ($row['institution_other'] ?? ''));
                    $institutionPacked = $institutionOther;
                    if ($place !== '') {
                        $institutionPacked = $institutionOther !== '' ? ($institutionOther . ' | ' . $place) : ('- | ' . $place);
                    }

                    $licenseNotApplicable = ! empty($row['license_not_applicable']);
                    $localization->educations()->create([
                        'institution_id' => $row['institution_id'] ?? null,
                        'institution_other' => $institutionPacked !== '' ? $institutionPacked : null,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'start_year' => $startDate ? (int) Carbon::parse($startDate)->format('Y') : null,
                        'end_year' => $endDate ? (int) Carbon::parse($endDate)->format('Y') : null,
                        'is_ongoing' => ! empty($row['is_ongoing']),
                        'completion_date' => $completionDate,
                        'year_completed' => $completionDate ? (int) Carbon::parse($completionDate)->format('Y') : null,
                        'degree_id' => $row['degree_id'] ?? null,
                        'degree_other' => $row['degree_other'] ?? null,
                        'license_number' => $licenseNotApplicable ? null : ($row['license_number'] ?? null),
                        'license_not_applicable' => $licenseNotApplicable,
                        'sort_order' => $index + 1,
                    ]);
                }

                $localization->gcpCertifications()->delete();
                $gcpRows = $payload['gcps'] ?? [];

                foreach ($gcpRows as $index => $row) {
                    $noExpiration = ! empty($row['no_expiration']);
                    $expiresAt = $noExpiration ? null : ($row['expires_at'] ?? null);
                    $hasData = ! empty($row['provider'])
                        || ! empty($row['course_name'])
                        || ! empty($row['guideline_version'])
                        || ! empty($row['certificate_language'])
                        || ! empty($row['issued_at'])
                        || ! empty($expiresAt)
                        || $noExpiration;

                    if (! $hasData) {
                        continue;
                    }

                    $status = $noExpiration ? 'unknown' : $this->resolveGcpStatus($expiresAt);

                    $localization->gcpCertifications()->create([
                        'provider' => $row['provider'] ?? null,
                        'course_name' => $row['course_name'] ?? null,
                        'guideline_version' => $row['guideline_version'] ?? null,
                        'certificate_language' => $row['certificate_language'] ?? null,
                        'certificate_id' => null,
                        'issued_at' => $row['issued_at'] ?? null,
                        'expires_at' => $expiresAt,
                        'no_expiration' => $noExpiration,
                        'status' => $status,
                        'verification_url' => null,
                        'certificate_file_path' => null,
                        'notes' => null,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            $this->createVersionSnapshot($cv);
        });

        return redirect('/cvs/me')->with('ok', 'CV guardado con educación y catálogos.');
    }

    public function restoreVersion(CvVersion $version): RedirectResponse
    {
        $cv = $this->resolveCv();
        if ($version->cv_id !== $cv->id) {
            abort(403);
        }

        $snapshot = $version->snapshot_json ?? [];

        DB::transaction(function () use ($cv, $snapshot) {
            $localizations = $snapshot['localizations'] ?? [];

            foreach (['es', 'en'] as $locale) {
                $row = $localizations[$locale] ?? [];
                $loc = CvLocalization::updateOrCreate(
                    ['cv_id' => $cv->id, 'locale' => $locale],
                    [
                        'title_name' => $row['title_name'] ?? null,
                        'office_phone' => $row['office_phone'] ?? null,
                        'fax_number' => $row['fax_number'] ?? null,
                        'email' => $row['email'] ?? null,
                        'position_label' => $row['position_label'] ?? null,
                        'summary_text' => $row['summary_text'] ?? null,
                        'professional_experience_json' => $row['professional_experience_json'] ?? [],
                        'clinical_research_json' => $row['clinical_research_json'] ?? [],
                        'trainings_json' => $row['trainings_json'] ?? [],
                    ]
                );

                $loc->educations()->delete();
                foreach (($row['educations'] ?? []) as $idx => $edu) {
                    $loc->educations()->create([
                        'institution_id' => $edu['institution_id'] ?? null,
                        'institution_other' => $edu['institution_other'] ?? null,
                        'start_date' => $edu['start_date'] ?? null,
                        'end_date' => $edu['end_date'] ?? null,
                        'start_year' => $edu['start_year'] ?? null,
                        'end_year' => $edu['end_year'] ?? null,
                        'is_ongoing' => ! empty($edu['is_ongoing']),
                        'completion_date' => $edu['completion_date'] ?? null,
                        'year_completed' => $edu['year_completed'] ?? null,
                        'degree_id' => $edu['degree_id'] ?? null,
                        'degree_other' => $edu['degree_other'] ?? null,
                        'license_number' => $edu['license_number'] ?? null,
                        'license_not_applicable' => ! empty($edu['license_not_applicable']),
                        'sort_order' => $idx + 1,
                    ]);
                }

                $loc->gcpCertifications()->delete();
                foreach (($row['gcps'] ?? []) as $idx => $gcp) {
                    $loc->gcpCertifications()->create([
                        'provider' => $gcp['provider'] ?? null,
                        'course_name' => $gcp['course_name'] ?? null,
                        'guideline_version' => $gcp['guideline_version'] ?? null,
                        'certificate_language' => $gcp['certificate_language'] ?? null,
                        'certificate_id' => $gcp['certificate_id'] ?? null,
                        'issued_at' => $gcp['issued_at'] ?? null,
                        'expires_at' => $gcp['expires_at'] ?? null,
                        'no_expiration' => ! empty($gcp['no_expiration']),
                        'status' => $gcp['status'] ?? $this->resolveGcpStatus($gcp['expires_at'] ?? null),
                        'verification_url' => $gcp['verification_url'] ?? null,
                        'certificate_file_path' => $gcp['certificate_file_path'] ?? null,
                        'notes' => $gcp['notes'] ?? null,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }

            $cv->status = $snapshot['status'] ?? $cv->status;
            $cv->save();
            $this->createVersionSnapshot($cv);
        });

        return redirect('/cvs/me')->with('ok', 'Versión restaurada: v' . $version->version_number . '.');
    }

    public function importWord(Request $request): RedirectResponse
    {
        $request->validate([
            'cv_file' => ['required', 'file', 'mimes:doc,docx', 'max:10240'],
        ]);

        $file = $request->file('cv_file');
        $storedPath = $file->store('cv-imports', 'local');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $filenameLower = Str::lower($originalName);

        $absolutePath = Storage::disk('local')->path($storedPath);
        if (! is_file($absolutePath)) {
            // Fallback for legacy path assumptions.
            $legacyPath = storage_path('app/' . $storedPath);
            if (is_file($legacyPath)) {
                $absolutePath = $legacyPath;
            }
        }
        $text = '';
        $notes = null;

        // On macOS textutil reliably extracts text from both .doc and .docx.
        $text = $this->extractTextWithTextutil($absolutePath);
        if ($text === '' && $extension === 'docx') {
            $text = $this->extractTextFromDocx($absolutePath);
        }

        if ($text === '') {
            $notes = 'No se logró extraer texto del archivo. Intenta guardarlo nuevamente desde Word y vuelve a subir.';
        } elseif ($extension === 'doc') {
            $notes = 'Archivo .doc importado. Si notas faltantes, conviértelo a .docx para mejor precisión.';
        }

        $detected = $this->detectLocale($text, $filenameLower);
        $fields = $this->extractBasicFields($text, $detected['locale']);

        $cv = $this->resolveCv();
        $targetLocale = in_array($detected['locale'], ['es', 'en'], true) ? $detected['locale'] : 'es';

        $localization = CvLocalization::firstOrCreate(
            ['cv_id' => $cv->id, 'locale' => $targetLocale],
            ['title_name' => null, 'email' => null]
        );

        $localization->update([
            'title_name' => $fields['title_name'] ?: $localization->title_name,
            'email' => $fields['email'] ?: $localization->email,
            'position_label' => $fields['position_label'] ?: $localization->position_label,
            'summary_text' => $fields['summary_text'] ?: $localization->summary_text,
        ]);

        if (! $fields['title_name'] && ! $fields['email'] && ! $fields['position_label']) {
            $notes = trim(($notes ? $notes . ' ' : '') . 'Se detectó idioma, pero no se localizaron campos clave para autocompletar.');
        }

        CvImportJob::create([
            'user_id' => auth()->id(),
            'original_file_name' => $originalName,
            'stored_file_path' => $storedPath,
            'detected_locale' => $detected['locale'],
            'confidence_score' => $detected['confidence'],
            'parse_status' => 'ready_for_review',
            'notes' => $notes,
        ]);

        return redirect('/cvs/me')->with('ok', 'Importación aplicada: idioma detectado ' . strtoupper($detected['locale']) . ' (' . $detected['confidence'] . '%). Revisa y corrige antes de publicar.');
    }

    public function publish(): RedirectResponse
    {
        $cv = $this->resolveCv();
        $cv->load('localizations');

        $es = $cv->localizations->firstWhere('locale', 'es');
        $en = $cv->localizations->firstWhere('locale', 'en');

        $esReady = $es && $es->title_name && $es->email;
        if (! $esReady) {
            return redirect('/cvs/me')->with('error', 'No se puede publicar: faltan campos obligatorios en español.');
        }

        $cv->status = 'published';
        $cv->last_published_at = Carbon::now();
        $cv->save();

        return redirect('/dashboard')->with('ok', 'CV publicado en español e inglés.');
    }

    public function downloadPdf(string $locale)
    {
        $cv = $this->resolveCv();
        return $this->buildPdfDownloadResponse($cv, $locale, '/cvs/me');
    }

    public function publishedIndex(): View
    {
        $cvs = Cv::query()
            ->with(['user:id,name,email', 'localizations:id,cv_id,locale,title_name,email,position_label'])
            ->where('status', 'published')
            ->orderByDesc('last_published_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('cvs.published', [
            'cvs' => $cvs,
        ]);
    }

    public function downloadPublishedPdf(Cv $cv, string $locale)
    {
        if ($cv->status !== 'published') {
            abort(403);
        }

        return $this->buildPdfDownloadResponse($cv, $locale, '/cvs/published');
    }

    public function destroyPublished(Cv $cv): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = (int) $cv->user_id === (int) $user->id;
        $isAdmin = method_exists($user, 'isAdmin') ? $user->isAdmin() : false;

        if (! $isOwner && ! $isAdmin) {
            abort(403);
        }

        $cv->delete();

        return redirect('/cvs/published')->with('ok', 'CV eliminado correctamente.');
    }

    private function buildPdfDownloadResponse(Cv $cv, string $locale, string $fallbackUrl)
    {
        if (! in_array($locale, ['es', 'en'], true)) {
            abort(404);
        }

        $cv->load(['localizations.educations', 'localizations.gcpCertifications']);
        $loc = $cv->localizations->firstWhere('locale', $locale);
        if (! $loc && $locale === 'en') {
            $loc = $cv->localizations->firstWhere('locale', 'es');
        }

        if (! $loc) {
            return redirect($fallbackUrl)->with('error', 'No existe información para generar PDF en ' . strtoupper($locale) . '.');
        }

        $signedAt = Carbon::now()->toIso8601String();
        $signerEmail = auth()->user()?->email ?? 'system@unamis.mx';
        $seal = $this->createDigitalSeal($cv, $loc, $locale, $signedAt, $signerEmail);
        $labels = $this->pdfLabels($locale);

        $pdf = Pdf::loadView('pdf.cv_template', [
            'cv' => $cv,
            'loc' => $loc,
            'labels' => $labels,
            'institutionalAddress' => config('cv.institutional_address'),
            'locale' => $locale,
            'seal' => $seal,
            'signatureDataUri' => $this->buildSignatureDataUri($cv->user),
        ])->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isPhpEnabled', true)
            // Reduce PDF size by embedding only used glyphs (no visual change expected).
            ->setOption('isFontSubsettingEnabled', true);

        $filename = 'CV_' . strtoupper($locale) . '.pdf';

        return $pdf->download($filename);
    }

    private function pdfLabels(string $locale): array
    {
        return $locale === 'es'
            ? [
                'title_name' => 'TÍTULO, NOMBRE Y APELLIDO',
                'institution' => 'DATOS DE LA INSTITUCIÓN',
                'office_phone' => 'TELÉFONO OFICINA',
                'fax' => 'FAX NUMBER',
                'email' => 'CORREO ELECTRÓNICO',
                'position' => 'PUESTO A DESEMPEÑAR EN EL ESTUDIO',
                'education' => 'EDUCACIÓN',
                'professional' => 'EXPERIENCIA PROFESIONAL',
                'clinical' => 'EXPERIENCIA EN INVESTIGACIÓN CLÍNICA',
                'training' => 'ENTRENAMIENTOS',
                'gcp' => 'CERTIFICACIONES GCP',
                'date' => 'FECHA',
                'signature' => 'FIRMA',
                'year' => 'AÑO',
                'edu_institution_col' => 'INSTITUCIÓN',
                'edu_place_col' => 'LUGAR',
                'edu_year_col' => 'AÑO',
                'edu_degree_col' => 'GRADO OBTENIDO',
                'edu_license_col' => 'CÉDULA / LICENCIA',
                'pro_institution_col' => 'INSTITUCIÓN',
                'pro_role_col' => 'CARGO',
                'pro_year_col' => 'AÑO(S)',
                'clinical_year_col' => 'AÑO',
                'clinical_therapeutic_col' => 'INDICACIÓN TERAPÉUTICA',
                'clinical_role_col' => 'CARGO',
                'clinical_phase_col' => 'FASE',
                'training_course_col' => 'CURSO',
                'training_place_col' => 'MODALIDAD',
                'training_completion_col' => 'FECHA EXPEDICIÓN',
                'gcp_provider_col' => 'PROVEEDOR',
                'gcp_course_col' => 'CURSO',
                'gcp_version_col' => 'VERSIÓN',
                'gcp_issued_col' => 'EMISIÓN',
                'gcp_expires_col' => 'VENCIMIENTO',
                'gcp_status_col' => 'ESTATUS',
                'page_word' => 'Página',
                'page_of' => 'de',
            ]
            : [
                'title_name' => 'TITLE, NAME (FIRST, MIDDLE, LAST)',
                'institution' => 'INSTITUTION DATA',
                'office_phone' => 'TELEPHONE NUMBER',
                'fax' => 'FAX NUMBER',
                'email' => 'E-MAIL',
                'position' => 'POSITION',
                'education' => 'EDUCATION AND TRAINING',
                'professional' => 'PROFESSIONAL EXPERIENCE',
                'clinical' => 'CLINICAL INVESTIGATION RESEARCH',
                'training' => 'TRAINING DOCUMENTATION',
                'gcp' => 'GCP CERTIFICATIONS',
                'date' => 'DATE',
                'signature' => 'SIGNATURE',
                'year' => 'YEAR',
                'edu_institution_col' => 'INSTITUTION',
                'edu_place_col' => 'PLACE',
                'edu_year_col' => 'YEAR',
                'edu_degree_col' => 'DEGREE',
                'edu_license_col' => 'LICENSE / REGISTRY',
                'pro_institution_col' => 'INSTITUTION',
                'pro_role_col' => 'POSITION',
                'pro_year_col' => 'YEAR',
                'clinical_year_col' => 'YEAR',
                'clinical_therapeutic_col' => 'THERAPEUTICAL AREA',
                'clinical_role_col' => 'POSITION',
                'clinical_phase_col' => 'PHASE',
                'training_course_col' => 'COURSE PROVIDER',
                'training_place_col' => 'MODALITY',
                'training_completion_col' => 'COMPLETION DATE',
                'gcp_provider_col' => 'PROVIDER',
                'gcp_course_col' => 'COURSE',
                'gcp_version_col' => 'VERSION',
                'gcp_issued_col' => 'ISSUED',
                'gcp_expires_col' => 'EXPIRES',
                'gcp_status_col' => 'STATUS',
                'page_word' => 'Page',
                'page_of' => 'of',
            ];
    }

    public function verifySeal(Request $request): View
    {
        $data = $request->validate([
            'folio' => ['required', 'string', 'max:40'],
        ]);

        $seal = CvDocumentSeal::where('folio', $data['folio'])->first();
        if (! $seal) {
            return view('cvs.verify', [
                'signatureValid' => false,
                'hashMatchesCurrent' => false,
                'data' => ['folio' => $data['folio']],
                'found' => false,
            ]);
        }

        $payload = implode('|', [
            $seal->cv_id,
            $seal->locale,
            $seal->hash_sha256,
            $seal->signed_at->toIso8601String(),
            $seal->signer_email,
        ]);

        $expectedSig = hash_hmac('sha256', $payload, $this->sealSecret());
        $signatureValid = hash_equals($expectedSig, $seal->signature_hmac);

        $cv = Cv::find($seal->cv_id);
        $hashMatchesCurrent = false;
        if ($cv) {
            $cv->load(['localizations.educations', 'localizations.gcpCertifications']);
            $loc = $cv->localizations->firstWhere('locale', $seal->locale);
            if ($loc) {
                $currentHash = $this->buildCvHash($cv, $loc, $seal->locale);
                $hashMatchesCurrent = hash_equals($currentHash, $seal->hash_sha256);
            }
        }

        return view('cvs.verify', [
            'signatureValid' => $signatureValid,
            'hashMatchesCurrent' => $hashMatchesCurrent,
            'found' => true,
            'data' => [
                'folio' => $seal->folio,
                'cv' => $seal->cv_id,
                'locale' => $seal->locale,
                'signer' => $seal->signer_email,
                'signed_at' => $seal->signed_at->toIso8601String(),
                'hash' => $seal->hash_sha256,
                'sig' => $seal->signature_hmac,
            ],
        ]);
    }

    private function normalizeRows(array $rows, array $allowedKeys): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $record = [];
            foreach ($allowedKeys as $key) {
                if ($key === 'is_ongoing') {
                    $record[$key] = ! empty($row[$key]);
                    continue;
                }
                $record[$key] = isset($row[$key]) ? trim((string) $row[$key]) : null;
            }

            $hasData = false;
            foreach ($record as $value) {
                if ($value === true || (is_string($value) && $value !== '') || (is_numeric($value) && $value !== '')) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $normalized[] = $record;
            }
        }

        return $normalized;
    }

    private function normalizeMexOfficePhone(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '52') && strlen($digits) > 10) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) > 10) {
            $digits = substr($digits, -10);
        }

        if (strlen($digits) !== 10) {
            return null;
        }

        return sprintf('+52 (%s) %s', substr($digits, 0, 3), substr($digits, 3, 7));
    }

    private function resolveCv(): Cv
    {
        return Cv::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'status' => 'draft',
                'institutional_address' => config('cv.institutional_address'),
            ]
        );
    }

    private function createVersionSnapshot(Cv $cv): void
    {
        $cv->load(['localizations.educations', 'localizations.gcpCertifications']);
        $payload = [
            'status' => $cv->status,
            'localizations' => [],
        ];

        foreach ($cv->localizations as $loc) {
            $payload['localizations'][$loc->locale] = [
                'title_name' => $loc->title_name,
                'office_phone' => $loc->office_phone,
                'fax_number' => $loc->fax_number,
                'email' => $loc->email,
                'position_label' => $loc->position_label,
                'summary_text' => $loc->summary_text,
                'professional_experience_json' => $loc->professional_experience_json ?? [],
                'clinical_research_json' => $loc->clinical_research_json ?? [],
                'trainings_json' => $loc->trainings_json ?? [],
                'educations' => $loc->educations->map(fn ($e) => [
                    'institution_id' => $e->institution_id,
                    'institution_other' => $e->institution_other,
                    'start_date' => optional($e->start_date)->format('Y-m-d'),
                    'end_date' => optional($e->end_date)->format('Y-m-d'),
                    'start_year' => $e->start_year,
                    'end_year' => $e->end_year,
                    'is_ongoing' => $e->is_ongoing,
                    'completion_date' => optional($e->completion_date)->format('Y-m-d'),
                    'year_completed' => $e->year_completed,
                    'degree_id' => $e->degree_id,
                    'degree_other' => $e->degree_other,
                    'license_number' => $e->license_number,
                    'license_not_applicable' => (bool) $e->license_not_applicable,
                ])->values()->toArray(),
                'gcps' => $loc->gcpCertifications->map(fn ($g) => [
                    'provider' => $g->provider,
                    'course_name' => $g->course_name,
                    'guideline_version' => $g->guideline_version,
                    'certificate_language' => $g->certificate_language,
                    'certificate_id' => $g->certificate_id,
                    'issued_at' => optional($g->issued_at)->format('Y-m-d'),
                    'expires_at' => optional($g->expires_at)->format('Y-m-d'),
                    'no_expiration' => (bool) $g->no_expiration,
                    'status' => $g->status,
                    'verification_url' => $g->verification_url,
                    'certificate_file_path' => $g->certificate_file_path,
                    'notes' => $g->notes,
                ])->values()->toArray(),
            ];
        }

        $nextVersion = ((int) CvVersion::where('cv_id', $cv->id)->max('version_number')) + 1;
        CvVersion::create([
            'cv_id' => $cv->id,
            'version_number' => $nextVersion,
            'snapshot_json' => $payload,
            'created_by' => auth()->id(),
        ]);
    }

    private function createDigitalSeal(Cv $cv, CvLocalization $loc, string $locale, string $signedAt, string $signerEmail): array
    {
        $hash = $this->buildCvHash($cv, $loc, $locale);
        $signedAtCarbon = Carbon::parse($signedAt);
        $year = (int) $signedAtCarbon->format('Y');
        $sequence = ((int) CvDocumentSeal::where('year', $year)->where('locale', $locale)->max('sequence')) + 1;
        $folio = sprintf('UNAMIS-CV-%d-%s-%04d', $year, strtoupper($locale), $sequence);

        $payload = implode('|', [
            $cv->id,
            $locale,
            $hash,
            $signedAt,
            $signerEmail,
        ]);

        $signature = hash_hmac('sha256', $payload, $this->sealSecret());
        CvDocumentSeal::create([
            'folio' => $folio,
            'year' => $year,
            'sequence' => $sequence,
            'cv_id' => $cv->id,
            'locale' => $locale,
            'hash_sha256' => $hash,
            'signature_hmac' => $signature,
            'signed_at' => $signedAtCarbon,
            'signer_email' => $signerEmail,
        ]);

        $verificationUrl = URL::to('/cvs/verify') . '?' . http_build_query([
            'folio' => $folio,
        ]);

        return [
            'folio' => $folio,
            'hash' => $hash,
            'signature' => $signature,
            'signed_at' => $signedAt,
            'signer_email' => $signerEmail,
            'verification_url' => $verificationUrl,
            'qr_url' => 'https://quickchart.io/qr?size=120&text=' . urlencode($verificationUrl),
        ];
    }

    private function buildCvHash(Cv $cv, CvLocalization $loc, string $locale): string
    {
        $snapshot = [
            'cv_id' => $cv->id,
            'locale' => $locale,
            'title_name' => $loc->title_name,
            'office_phone' => $loc->office_phone,
            'fax_number' => $loc->fax_number,
            'email' => $loc->email,
            'position_label' => $loc->position_label,
            'summary_text' => $loc->summary_text,
            'address' => config('cv.institutional_address'),
            'educations' => $loc->educations->map(fn ($e) => [
                'institution_other' => $e->institution_other,
                'start_date' => optional($e->start_date)->format('Y-m-d'),
                'end_date' => optional($e->end_date)->format('Y-m-d'),
                'start_year' => $e->start_year,
                'end_year' => $e->end_year,
                'is_ongoing' => $e->is_ongoing,
                'completion_date' => optional($e->completion_date)->format('Y-m-d'),
                'degree_other' => $e->degree_other,
                'license_number' => $e->license_number,
                'license_not_applicable' => (bool) $e->license_not_applicable,
            ])->values()->toArray(),
            'gcp_certifications' => $loc->gcpCertifications->map(fn ($g) => [
                'provider' => $g->provider,
                'course_name' => $g->course_name,
                'guideline_version' => $g->guideline_version,
                'certificate_language' => $g->certificate_language,
                'certificate_id' => $g->certificate_id,
                'issued_at' => optional($g->issued_at)->format('Y-m-d'),
                'expires_at' => optional($g->expires_at)->format('Y-m-d'),
                'no_expiration' => (bool) $g->no_expiration,
                'status' => $g->status,
                'verification_url' => $g->verification_url,
                'certificate_file_path' => $g->certificate_file_path,
                'notes' => $g->notes,
            ])->values()->toArray(),
            'professional' => $loc->professional_experience_json ?? [],
            'clinical' => $loc->clinical_research_json ?? [],
            'trainings' => $loc->trainings_json ?? [],
        ];

        return hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function sealSecret(): string
    {
        $raw = config('app.key', 'unamis-fallback-key');
        return str_starts_with($raw, 'base64:') ? base64_decode(substr($raw, 7)) ?: $raw : $raw;
    }

    private function buildSignatureDataUri(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $storedPath = trim((string) ($user->signature_file_path ?? ''));
        if ($storedPath === '') {
            return null;
        }

        $absolute = storage_path('app/' . ltrim($storedPath, '/'));
        if (! is_file($absolute) || ! is_readable($absolute)) {
            return null;
        }

        $binary = @file_get_contents($absolute);
        if (! is_string($binary) || $binary === '') {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($binary);
    }

    private function extractTextFromDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        $xml = preg_replace('/<w:p[^>]*>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<w:tab\/>/', ' ', $xml) ?? $xml;

        return trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function extractTextWithTextutil(string $path): string
    {
        $cmd = 'textutil -convert txt -stdout ' . escapeshellarg($path) . ' 2>/dev/null';
        $output = @shell_exec($cmd);
        if (! is_string($output)) {
            return '';
        }

        return trim($output);
    }

    private function detectLocale(string $text, string $filenameLower = ''): array
    {
        $haystack = Str::lower(Str::ascii($text));
        $filenameLower = Str::ascii($filenameLower);
        $esKeywords = ['educacion', 'experiencia', 'investigacion', 'puesto', 'correo electronico', 'medico'];
        $enKeywords = ['education', 'experience', 'research', 'position', 'e-mail', 'subinvestigator'];

        if (str_contains($filenameLower, 'espanol') || str_contains($filenameLower, 'español')) {
            return ['locale' => 'es', 'confidence' => 90];
        }
        if (str_contains($filenameLower, 'ingles') || str_contains($filenameLower, 'english')) {
            return ['locale' => 'en', 'confidence' => 90];
        }

        $esScore = 0;
        foreach ($esKeywords as $word) {
            $esScore += substr_count($haystack, $word);
        }

        $enScore = 0;
        foreach ($enKeywords as $word) {
            $enScore += substr_count($haystack, $word);
        }

        if ($esScore === 0 && $enScore === 0) {
            return ['locale' => 'unknown', 'confidence' => 30];
        }

        $locale = $esScore >= $enScore ? 'es' : 'en';
        $confidence = min(99, 50 + abs($esScore - $enScore) * 8);

        return ['locale' => $locale, 'confidence' => $confidence];
    }

    private function extractBasicFields(string $text, string $locale): array
    {
        preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $emailMatch);
        $email = $emailMatch[0] ?? null;

        $position = null;
        if (preg_match('/(?:PUESTO|POSITION)[^\n]*\n+([^\n]+)/iu', $text, $positionMatch)) {
            $position = trim($positionMatch[1]);
        }

        $title = null;
        $lines = preg_split('/\R+/', $text) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (mb_strlen($line) < 5) {
                continue;
            }

            $isHeading = preg_match('/^(T[IÍ]TULO|TITLE|EDUCACI[ÓO]N|EDUCATION|EXPERIENCIA|EXPERIENCE|CENTRO|INSTITUTION)/iu', $line);
            if ($isHeading) {
                continue;
            }

            $title = $line;
            break;
        }

        // Common CV pattern: heading line followed by full name on next line.
        foreach ($lines as $index => $line) {
            $clean = trim($line);
            if (preg_match('/^(T[IÍ]TULO|TITLE)[^A-Z0-9]*$/iu', $clean)) {
                $candidate = trim($lines[$index + 1] ?? '');
                if (mb_strlen($candidate) > 5) {
                    $title = $candidate;
                    break;
                }
            }
        }

        $summary = null;

        return [
            'title_name' => $title,
            'email' => $email,
            'position_label' => $position,
            'summary_text' => $summary,
            'locale' => $locale,
        ];
    }

    private function resolveGcpStatus(?string $expiresAt): string
    {
        if (! $expiresAt) {
            return 'unknown';
        }

        $today = Carbon::now()->startOfDay();
        $expiry = Carbon::parse($expiresAt)->startOfDay();

        if ($expiry->lt($today)) {
            return 'expired';
        }

        if ($expiry->lte($today->copy()->addDays(60))) {
            return 'expiring_soon';
        }

        return 'valid';
    }

}
