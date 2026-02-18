# Importador masivo de carreras y especialidades

Comando:
```bash
php artisan catalog:import-degrees {source} {file} [--default-type=otro] [--dry-run] [--limit=1000]
```

## Fuentes soportadas
- `puem` (UNAM/PUEM u otra hoja homologada)
- `cifrhs` (catálogos de especialidades/plazas)
- `custom` (archivo maestro interno)

## Columnas esperadas (flexibles)
- Nombre en español: `nombre`, `nombre_es`, `especialidad`, `carrera`, `programa`
- Nombre en inglés (opcional): `name_en`, `nombre_en`, `english_name`
- Tipo (opcional): `tipo`, `categoria`, `nivel`, `degree_type`, `tipo_grado`, `area`
- ID externo (opcional): `id`, `clave`, `codigo`, `code`, `cve`

## Tipos finales del sistema
- `especialidad_medica`
- `carrera_salud`
- `carrera_tecnica`
- `bachillerato`
- `otro`

## Ejemplos
```bash
php artisan catalog:import-degrees custom "/Users/jriveramx/Downloads/carreras_especialidades.csv" --dry-run --limit=500
php artisan catalog:import-degrees custom "/Users/jriveramx/Downloads/carreras_especialidades.csv"

php artisan catalog:import-degrees puem "/Users/jriveramx/Downloads/puem_especialidades.xlsx" --default-type=especialidad_medica
php artisan catalog:import-degrees cifrhs "/Users/jriveramx/Downloads/cifrhs_catalogo.xlsx" --default-type=especialidad_medica
```

## Recomendación
Primero correr con `--dry-run`, revisar conteos y después ejecutar carga real.
