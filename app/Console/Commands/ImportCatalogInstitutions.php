<?php

namespace App\Console\Commands;

use App\Models\CatalogInstitution;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ZipArchive;

class ImportCatalogInstitutions extends Command
{
    protected $signature = 'catalog:import-institutions
        {source : sep|clues|custom}
        {file : Ruta del archivo CSV/XLSX}
        {--dry-run : Solo simula, no guarda}
        {--limit=0 : Limitar numero de filas procesadas}';

    protected $description = 'Importa catalogos masivos de instituciones desde fuentes oficiales (SEP/CLUES).';

    public function handle(): int
    {
        $source = Str::lower((string) $this->argument('source'));
        $fileArg = (string) $this->argument('file');
        $limit = max(0, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        if (! in_array($source, ['sep', 'clues', 'custom'], true)) {
            $this->error('Fuente no válida. Usa: sep, clues o custom.');
            return self::FAILURE;
        }

        $path = $this->resolvePath($fileArg);
        if (! is_file($path)) {
            $this->error('Archivo no encontrado: ' . $path);
            return self::FAILURE;
        }

        $rows = $this->loadRows($path);
        if (count($rows) === 0) {
            $this->error('No se encontraron filas en el archivo.');
            return self::FAILURE;
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;

        foreach ($rows as $row) {
            $processed++;
            if ($limit > 0 && $processed > $limit) {
                break;
            }

            $mapped = match ($source) {
                'sep' => $this->mapSepRow($row),
                'clues' => $this->mapCluesRow($row),
                default => $this->mapCustomRow($row),
            };

            if (! $mapped || empty($mapped['name'])) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $imported++;
                continue;
            }

            $query = null;
            if (! empty($mapped['external_source']) && ! empty($mapped['external_id'])) {
                $query = CatalogInstitution::where('external_source', $mapped['external_source'])
                    ->where('external_id', $mapped['external_id']);
            }

            if ($query && $query->exists()) {
                $model = $query->first();
                $model->fill($mapped)->save();
                $updated++;
                continue;
            }

            $byName = CatalogInstitution::where('name', $mapped['name'])
                ->where('state_name', $mapped['state_name'])
                ->where('institution_type', $mapped['institution_type'])
                ->first();

            if ($byName) {
                $byName->fill($mapped)->save();
                $updated++;
            } else {
                CatalogInstitution::create($mapped);
                $imported++;
            }
        }

        $this->info('Importación terminada.');
        $this->line('Procesadas: ' . min($processed, $limit > 0 ? $limit : $processed));
        $this->line('Nuevas: ' . $imported);
        $this->line('Actualizadas: ' . $updated);
        $this->line('Omitidas: ' . $skipped);
        $this->line('Modo: ' . ($dryRun ? 'DRY RUN' : 'REAL'));

        return self::SUCCESS;
    }

    private function resolvePath(string $fileArg): string
    {
        if (Str::startsWith($fileArg, ['/','./','../'])) {
            return realpath($fileArg) ?: $fileArg;
        }

        $candidate = base_path($fileArg);
        return realpath($candidate) ?: $candidate;
    }

    private function loadRows(string $path): array
    {
        $ext = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'csv', 'txt' => $this->loadCsvRows($path),
            'xlsx' => $this->loadXlsxRows($path),
            default => [],
        };
    }

    private function loadCsvRows(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return [];
        }

        $headers = null;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (! $headers) {
                $headers = $this->normalizeHeaders($data);
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function loadXlsxRows(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $shared = @simplexml_load_string($sharedXml);
            if ($shared && isset($shared->si)) {
                foreach ($shared->si as $si) {
                    $parts = [];
                    if (isset($si->t)) {
                        $parts[] = (string) $si->t;
                    }
                    if (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $parts[] = (string) ($r->t ?? '');
                        }
                    }
                    $sharedStrings[] = trim(implode('', $parts));
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! $sheetXml) {
            return [];
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (! $sheet || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $rowsRaw = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) ($c['r'] ?? '');
                $col = preg_replace('/\d+/', '', $ref) ?: '';
                $type = (string) ($c['t'] ?? '');

                $value = '';
                if (isset($c->v)) {
                    $value = (string) $c->v;
                }

                if ($type === 's') {
                    $idx = (int) $value;
                    $value = $sharedStrings[$idx] ?? '';
                }

                $cells[$col] = trim($value);
            }
            $rowsRaw[] = $cells;
        }

        if (count($rowsRaw) === 0) {
            return [];
        }

        $colHeaders = $rowsRaw[0];
        $colKeys = [];
        foreach ($colHeaders as $col => $headerValue) {
            $colKeys[$col] = $this->normalizeHeader($headerValue);
        }

        $rows = [];
        for ($i = 1; $i < count($rowsRaw); $i++) {
            $assoc = [];
            foreach ($rowsRaw[$i] as $col => $value) {
                $key = $colKeys[$col] ?? '';
                if ($key === '') {
                    continue;
                }
                $assoc[$key] = $value;
            }
            $rows[] = $assoc;
        }

        return $rows;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($h) => $this->normalizeHeader((string) $h), $headers);
    }

    private function normalizeHeader(string $header): string
    {
        $h = Str::lower(Str::ascii(trim($header)));
        $h = preg_replace('/\s+/', '_', $h) ?? $h;
        $h = preg_replace('/[^a-z0-9_]/', '', $h) ?? $h;
        return $h;
    }

    private function getValue(array $row, array $candidateHeaders): ?string
    {
        foreach ($candidateHeaders as $header) {
            $h = $this->normalizeHeader($header);
            if (array_key_exists($h, $row) && trim((string) $row[$h]) !== '') {
                return trim((string) $row[$h]);
            }
        }

        return null;
    }

    private function mapSepRow(array $row): ?array
    {
        $id = $this->getValue($row, ['cct', 'clave_centro_trabajo', 'clave_del_centro_de_trabajo']);
        $name = $this->getValue($row, ['nombre_ct', 'nombre_del_centro_de_trabajo', 'nombre']);
        $state = $this->getValue($row, ['entidad', 'estado', 'entidad_federativa']);
        $municipality = $this->getValue($row, ['municipio', 'alcaldia_municipio']);
        $city = $this->getValue($row, ['localidad', 'ciudad']);
        $level = $this->getValue($row, ['nivel_educativo', 'nivel', 'tipo_educativo', 'servicio']);

        if (! $name) {
            return null;
        }

        return [
            'institution_type' => $this->classifySepInstitutionType($level, $name),
            'name' => $name,
            'state_name' => $state,
            'municipality_name' => $municipality,
            'city_name' => $city,
            'country_name' => 'México',
            'external_source' => 'sep_cct',
            'external_id' => $id,
            'active' => true,
        ];
    }

    private function mapCluesRow(array $row): ?array
    {
        $id = $this->getValue($row, ['clues', 'clave_clues', 'claveestablecimiento']);
        $name = $this->getValue($row, ['nombre_unidad', 'nombre_establecimiento', 'nombre']);
        $state = $this->getValue($row, ['entidad', 'estado', 'nombre_entidad']);
        $municipality = $this->getValue($row, ['municipio', 'nombre_municipio']);
        $city = $this->getValue($row, ['localidad', 'nombre_localidad']);

        if (! $name) {
            return null;
        }

        return [
            'institution_type' => 'hospital',
            'name' => $name,
            'state_name' => $state,
            'municipality_name' => $municipality,
            'city_name' => $city,
            'country_name' => 'México',
            'external_source' => 'clues',
            'external_id' => $id,
            'active' => true,
        ];
    }

    private function classifySepInstitutionType(?string $level, string $name): string
    {
        $haystack = Str::lower(Str::ascii(trim(($level ?? '') . ' ' . $name)));

        if (str_contains($haystack, 'universidad') || str_contains($haystack, 'superior')) {
            return 'universidad';
        }

        if (str_contains($haystack, 'bachiller') || str_contains($haystack, 'preparatoria') || str_contains($haystack, 'media superior') || str_contains($haystack, 'cch') || str_contains($haystack, 'enp')) {
            return 'bachillerato';
        }

        if (str_contains($haystack, 'tecnica') || str_contains($haystack, 'tecnologico') || str_contains($haystack, 'conalep') || str_contains($haystack, 'cbtis') || str_contains($haystack, 'cetis')) {
            return 'tecnica';
        }

        return 'otro';
    }

    private function mapCustomRow(array $row): ?array
    {
        $id = $this->getValue($row, ['external_id', 'id', 'clave', 'codigo']);
        $name = $this->getValue($row, ['name', 'nombre', 'institucion']);
        $state = $this->getValue($row, ['state_name', 'estado', 'entidad']);
        $municipality = $this->getValue($row, ['municipality_name', 'municipio']);
        $city = $this->getValue($row, ['city_name', 'ciudad', 'localidad']);
        $country = $this->getValue($row, ['country_name', 'pais']) ?: 'México';
        $typeRaw = $this->getValue($row, ['institution_type', 'tipo']);
        $sourceRaw = $this->getValue($row, ['source', 'external_source']) ?: 'custom';

        if (! $name) {
            return null;
        }

        $type = Str::lower(Str::ascii((string) $typeRaw));
        $allowed = ['universidad', 'bachillerato', 'tecnica', 'hospital', 'otro'];
        if (! in_array($type, $allowed, true)) {
            $type = 'otro';
        }

        return [
            'institution_type' => $type,
            'name' => $name,
            'state_name' => $state,
            'municipality_name' => $municipality,
            'city_name' => $city,
            'country_name' => $country,
            'external_source' => Str::lower(Str::ascii($sourceRaw)),
            'external_id' => $id,
            'active' => true,
        ];
    }
}
