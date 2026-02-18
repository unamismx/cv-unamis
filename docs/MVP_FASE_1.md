# MVP Fase 1 (4 semanas)

## Objetivo
Tener un gestor funcional donde cada usuario cree y actualice su CV con version `ES` y `EN`, genere PDF firmable y disponible para revisores/admin.

## Semana 1 - Base del sistema
- Autenticacion y roles (`usuario`, `revisor`, `admin`).
- Estructura de CV bilingue.
- Regla de domicilio institucional fijo.
- Dashboard estilo workspace con lista de CVs y estado.

## Semana 2 - Captura y catalogos
- Formularios por seccion: datos generales, educacion, experiencia, investigacion, entrenamientos.
- Catalogos desplegables (instituciones y grados/carreras).
- Opcion `Otro` en todos los catalogos relevantes.
- Validaciones de campos requeridos y coherencia de fechas.

## Semana 3 - Firma y PDF
- Captura de firma en pad + carga de firma escaneada.
- Ajuste de firma (dimension/posicion) para plantilla.
- Generador PDF ES y EN.
- Sello digital de documento: hash + folio + QR validable.

## Semana 4 - Importador Word y cierre
- Carga de `.doc/.docx` antiguo.
- Deteccion de idioma y prellenado con score de confianza.
- Pantalla de revision previa a guardar.
- Pruebas funcionales y salida a produccion.

## Criterios de exito del MVP
- No existe CV `published` sin `es` y `en`.
- Domicilio fijo no editable en UI ni API.
- PDF con firma visible y QR verificable.
- Importador reduce captura manual en al menos 60% en pruebas internas.
