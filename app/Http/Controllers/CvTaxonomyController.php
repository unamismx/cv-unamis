<?php

namespace App\Http\Controllers;

use App\Models\CvTaxonomyTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvTaxonomyController extends Controller
{
    public function index(): View
    {
        $terms = CvTaxonomyTerm::query()
            ->orderBy('taxonomy_type')
            ->orderBy('sort_order')
            ->orderBy('name_es')
            ->get()
            ->groupBy('taxonomy_type');

        return view('admin.cv-taxonomies', [
            'terms' => $terms,
            'taxonomyTypes' => $this->taxonomyTypeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'taxonomy_type' => ['required', 'string', 'in:' . implode(',', CvTaxonomyTerm::availableTypes())],
            'name_es' => ['required', 'string', 'max:180'],
            'name_en' => ['nullable', 'string', 'max:180'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'active' => ['nullable', 'boolean'],
        ]);

        CvTaxonomyTerm::updateOrCreate(
            [
                'taxonomy_type' => $data['taxonomy_type'],
                'name_es' => trim((string) $data['name_es']),
            ],
            [
                'name_en' => trim((string) ($data['name_en'] ?? '')) ?: null,
                'sort_order' => (int) ($data['sort_order'] ?? 999),
                'active' => ! empty($data['active']),
            ]
        );

        return redirect('/admin/cv-taxonomies')->with('ok', 'Catálogo actualizado.');
    }

    public function update(Request $request, CvTaxonomyTerm $term): RedirectResponse
    {
        $data = $request->validate([
            'name_es' => ['required', 'string', 'max:180'],
            'name_en' => ['nullable', 'string', 'max:180'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'active' => ['nullable', 'boolean'],
        ]);

        $term->update([
            'name_es' => trim((string) $data['name_es']),
            'name_en' => trim((string) ($data['name_en'] ?? '')) ?: null,
            'sort_order' => (int) ($data['sort_order'] ?? $term->sort_order ?: 999),
            'active' => ! empty($data['active']),
        ]);

        return redirect('/admin/cv-taxonomies')->with('ok', 'Registro actualizado.');
    }

    public function destroy(CvTaxonomyTerm $term): RedirectResponse
    {
        $term->delete();

        return redirect('/admin/cv-taxonomies')->with('ok', 'Registro eliminado.');
    }

    private function taxonomyTypeLabels(): array
    {
        return [
            CvTaxonomyTerm::TYPE_PROFESSIONS => 'Profesiones',
            CvTaxonomyTerm::TYPE_STUDY_POSITIONS => 'Puesto en estudio',
            CvTaxonomyTerm::TYPE_STUDY_ROLES => 'Rol en investigación',
            CvTaxonomyTerm::TYPE_THERAPEUTIC_AREAS => 'Indicación terapéutica',
            CvTaxonomyTerm::TYPE_EDUCATION_DEGREES => 'Grado obtenido (Educación)',
        ];
    }
}
