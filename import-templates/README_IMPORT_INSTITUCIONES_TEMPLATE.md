# Plantilla unificada de instituciones (SEP/CLUES/Custom)

Archivo: `plantilla_instituciones_sep_clues.csv`

## Columnas
- `source` (recomendado): `sep_cct`, `clues` o `custom`
- `external_id` (recomendado): CCT, CLUES o clave interna
- `institution_type` (obligatorio): `universidad`, `bachillerato`, `tecnica`, `hospital`, `otro`
- `name` (obligatorio): nombre de institución
- `state_name` (opcional)
- `municipality_name` (opcional)
- `city_name` (opcional)
- `country_name` (opcional, default: México)

## Importación con plantilla custom
```bash
cd /Users/jriveramx/Documents/cv-unamis-src
php artisan catalog:import-institutions custom "/Users/jriveramx/Documents/cv-unamis-src/import-templates/plantilla_instituciones_sep_clues.csv" --dry-run
php artisan catalog:import-institutions custom "/Users/jriveramx/Documents/cv-unamis-src/import-templates/plantilla_instituciones_sep_clues.csv"
```

## Recomendación práctica
- Para cargas oficiales masivas: usa archivos originales SEP/CLUES con `source=sep` o `source=clues`.
- Para ajustes manuales puntuales: usa esta plantilla con `source=custom`.
