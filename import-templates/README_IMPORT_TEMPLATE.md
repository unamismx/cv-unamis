# Plantilla oficial de importación: carreras y especialidades

Archivo: `plantilla_carreras_especialidades.csv`

## Columnas
- `id` (opcional, pero recomendado): clave única externa
- `nombre_es` (obligatorio): nombre en español
- `nombre_en` (opcional): nombre en inglés
- `tipo` (opcional):
  - `especialidad_medica`
  - `carrera_salud`
  - `carrera_tecnica`
  - `bachillerato`
  - `otro`

## Importación
```bash
cd /Users/jriveramx/Documents/cv-unamis-src
php artisan catalog:import-degrees custom "/Users/jriveramx/Documents/cv-unamis-src/import-templates/plantilla_carreras_especialidades.csv" --dry-run
php artisan catalog:import-degrees custom "/Users/jriveramx/Documents/cv-unamis-src/import-templates/plantilla_carreras_especialidades.csv"
```

## Reglas
- Si `id` ya existe para la misma fuente, actualiza.
- Si no hay `id`, intenta por `nombre_es + tipo`.
- Para evitar duplicados, mantener nombres consistentes.
