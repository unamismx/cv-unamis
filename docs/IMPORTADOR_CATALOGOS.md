# Importador masivo de catálogos

Comando:
```bash
php artisan catalog:import-institutions {source} {file} [--dry-run] [--limit=1000]
```

## Fuentes soportadas
- `sep`  -> Catálogo CCT SEP (CSV/XLSX)
- `clues` -> Catálogo CLUES salud (CSV/XLSX)

## Ejemplos
```bash
php artisan catalog:import-institutions sep "/Users/jriveramx/Downloads/sep_cct.csv" --dry-run --limit=500
php artisan catalog:import-institutions sep "/Users/jriveramx/Downloads/sep_cct.csv"

php artisan catalog:import-institutions clues "/Users/jriveramx/Downloads/clues.xlsx" --dry-run
php artisan catalog:import-institutions clues "/Users/jriveramx/Downloads/clues.xlsx"
```

## Comportamiento
- Si encuentra `external_source + external_id`, actualiza.
- Si no, intenta coincidir por `name + state + institution_type`.
- Si no existe, crea registro nuevo.

## Tipos de institución inferidos (SEP)
- `universidad`
- `bachillerato`
- `tecnica`
- `otro`

## Nota
Para cargas grandes, primero ejecutar `--dry-run` y revisar conteos.
