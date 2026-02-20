<?php

use App\Models\CvTaxonomyTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['es' => 'Licenciatura en Médico Cirujano', 'en' => 'Bachelor in Medical Doctor and Surgeon'],
            ['es' => 'Licenciatura en Enfermería', 'en' => 'Bachelor in Nursing'],
            ['es' => 'Licenciatura en Nutrición', 'en' => 'Bachelor in Nutrition'],
            ['es' => 'Licenciatura en Psicología', 'en' => 'Bachelor in Psychology'],
            ['es' => 'Licenciatura en Odontología', 'en' => 'Bachelor in Dentistry'],
            ['es' => 'Licenciatura en Químico Farmacéutico Biólogo', 'en' => 'Bachelor in Pharmaceutical Chemist Biologist'],
            ['es' => 'Especialidad Médica', 'en' => 'Medical Specialty'],
            ['es' => 'Maestría', 'en' => 'Master Degree'],
            ['es' => 'Doctorado', 'en' => 'Doctoral Degree'],
            ['es' => 'Técnico en Enfermería', 'en' => 'Nursing Technician'],
            ['es' => 'Bachillerato', 'en' => 'High School Diploma'],
        ];

        foreach ($rows as $index => $row) {
            DB::table('cv_taxonomy_terms')->updateOrInsert(
                [
                    'taxonomy_type' => CvTaxonomyTerm::TYPE_EDUCATION_DEGREES,
                    'name_es' => $row['es'],
                ],
                [
                    'name_en' => $row['en'],
                    'sort_order' => $index + 1,
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('cv_taxonomy_terms')
            ->where('taxonomy_type', CvTaxonomyTerm::TYPE_EDUCATION_DEGREES)
            ->delete();
    }
};
