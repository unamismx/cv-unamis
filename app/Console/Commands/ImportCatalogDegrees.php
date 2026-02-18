<?php

namespace App\Console\Commands;

use App\Models\CatalogDegree;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ZipArchive;

class ImportCatalogDegrees extends Command
{
    protected $signature = 'catalog:import-degrees
        {source : puem|cifrhs|custom}
        {file : Ruta del archivo CSV/XLSX}
        {--default-type=otro : Tipo por defecto si no viene en archivo}
        {--dry-run : Solo simula, no guarda}
        {--limit=0 : Limitar numero de filas procesadas}';

    protected $description = 'Importa catalogos masivos de carreras/especialidades desde archivo oficial o maestro interno.';

    public function handle(): int
    {
        $source = Str::lower((string) $this->argument('source'));
        $fileArg = (string) $this->argument('file');
        $defaultType = Str::lower((string) $this->option('default-type'));
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));

        if (! in_array($source, ['puem', 'cifrhs', 'custom'], true)) {
            $this->error('Fuente no válida. Usa: puem, cifrhs o custom.');
            return self::FAILURE;
        }

        $allowedTypes = ['especialidad_medica', 'carrera_salud', 'carrera_tecnica', 'bachillerato', 'otro'];
        if (! in_array($defaultType, $allowedTypes, true)) {
            $this->error('default-type inválido. Usa: ' . implode(', ', $allowedTypes));
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

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;

        foreach ($rows as $row) {
            $processed++;
            if ($limit > 0 && $processed > $limit) {
                break;
            }

            $mapped = $this->mapDegreeRow($row, $source, $defaultType);
            if (! $mapped || empty($mapped['name_es'])) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $inserted++;
                continue;
            }

            $query = null;
            if (! empty($mapped['external_source']) && ! empty($mapped['external_id'])) {
                $query = CatalogDegree::where('external_source', $mapped['external_source'])
                    ->where('external_id', $mapped['external_id']);
            }

            if ($query && $query->exists()) {
                $model = $query->first();
                $model->fill($mapped)->save();
                $updated++;
                continue;
            }

            $byName = CatalogDegree::where('name_es', $mapped['name_es'])
                ->where('degree_type', $mapped['degree_type'])
                ->first();

            if ($byName) {
                $byName->fill($mapped)->save();
                $updated++;
            } else {
                CatalogDegree::create($mapped);
                $inserted++;
            }
        }

        $this->info('Importación de carreras/especialidades completada.');
        $this->line('Procesadas: ' . min($processed, $limit > 0 ? $limit : $processed));
        $this->line('Nuevas: ' . $inserted);
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

    private function mapDegreeRow(array $row, string $source, string $defaultType): ?array
    {
        $externalId = $this->getValue($row, ['id', 'clave', 'codigo', 'code', 'cve']);
        $nameEs = $this->getValue($row, ['name_es', 'nombre_es', 'nombre', 'especialidad', 'carrera', 'programa']);
        $nameEn = $this->getValue($row, ['name_en', 'nombre_en', 'english_name']);
        $rawType = $this->getValue($row, ['degree_type', 'tipo', 'categoria', 'nivel', 'tipo_grado', 'area']) ?? '';

        if (! $nameEs) {
            return null;
        }

        $type = $this->normalizeDegreeType($rawType, $nameEs, $defaultType);

        return [
            'degree_type' => $type,
            'name_es' => $nameEs,
            'name_en' => $nameEn,
            'external_source' => $source,
            'external_id' => $externalId,
            'active' => true,
        ];
    }

    private function normalizeDegreeType(string $rawType, string $name, string $defaultType): string
    {
        $type = Str::lower(Str::ascii(trim($rawType)));
        $nameNorm = Str::lower(Str::ascii(trim($name)));

        if ($type !== '') {
            if (str_contains($type, 'especialidad')) return 'especialidad_medica';
            if (str_contains($type, 'medic')) return 'carrera_salud';
            if (str_contains($type, 'salud')) return 'carrera_salud';
            if (str_contains($type, 'tecnica') || str_contains($type, 'tecnico')) return 'carrera_tecnica';
            if (str_contains($type, 'bachiller')) return 'bachillerato';
            if (in_array($type, ['especialidad_medica', 'carrera_salud', 'carrera_tecnica', 'bachillerato', 'otro'], true)) {
                return $type;
            }
        }

        if (preg_match('/(cirujano|medicina|enfermer|nutric|odont|fisioter|qfb|farmac|salud)/', $nameNorm)) {
            return 'carrera_salud';
        }
        if (preg_match('/(tecnico|laboratorista|paramedico|radiologia|imagen)/', $nameNorm)) {
            return 'carrera_tecnica';
        }
        if (preg_match('/(especialidad|subespecialidad|cardiologia|nefrologia|pediatria|oncologia|infectologia)/', $nameNorm)) {
            return 'especialidad_medica';
        }
        if (preg_match('/(bachiller|preparatoria)/', $nameNorm)) {
            return 'bachillerato';
        }

        return $defaultType;
    }
}
