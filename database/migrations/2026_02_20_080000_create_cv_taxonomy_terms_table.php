<?php

use App\Models\CvTaxonomyTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->string('taxonomy_type', 40);
            $table->string('name_es', 180);
            $table->string('name_en', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['taxonomy_type', 'name_es']);
            $table->index(['taxonomy_type', 'active', 'sort_order']);
        });

        $seed = [
            CvTaxonomyTerm::TYPE_PROFESSIONS => [
                ['es' => 'Médico', 'en' => 'Medical Doctor'],
                ['es' => 'Médico Familiar', 'en' => 'Family Doctor'],
                ['es' => 'Médico General', 'en' => 'General Practitioner'],
                ['es' => 'Enfermería', 'en' => 'Nursing'],
                ['es' => 'Nutrición', 'en' => 'Nutrition'],
                ['es' => 'Psicología', 'en' => 'Psychology'],
                ['es' => 'QFB', 'en' => 'Pharmaceutical Chemist Biologist'],
                ['es' => 'Odontología', 'en' => 'Dentistry'],
                ['es' => 'Administración', 'en' => 'Administration'],
                ['es' => 'Contabilidad', 'en' => 'Accounting'],
                ['es' => 'Ingeniería', 'en' => 'Engineering'],
            ],
            CvTaxonomyTerm::TYPE_STUDY_POSITIONS => [
                ['es' => 'Investigador Principal', 'en' => 'Principal Investigator'],
                ['es' => 'Subinvestigador', 'en' => 'Subinvestigator'],
                ['es' => 'Coordinador de estudio', 'en' => 'Study Coordinator'],
                ['es' => 'Director', 'en' => 'Director'],
                ['es' => 'Médico del estudio', 'en' => 'Study Physician'],
                ['es' => 'Enfermera de investigación', 'en' => 'Research Nurse'],
                ['es' => 'Capturista de datos', 'en' => 'Data Entry Specialist'],
                ['es' => 'Farmacéutico', 'en' => 'Pharmacist'],
                ['es' => 'Laboratorio', 'en' => 'Laboratory Staff'],
            ],
            CvTaxonomyTerm::TYPE_STUDY_ROLES => [
                ['es' => 'Investigador Principal', 'en' => 'Principal Investigator'],
                ['es' => 'Subinvestigador', 'en' => 'Subinvestigator'],
                ['es' => 'Coordinador de estudio', 'en' => 'Study Coordinator'],
                ['es' => 'Monitor', 'en' => 'Monitor'],
                ['es' => 'Data manager', 'en' => 'Data Manager'],
                ['es' => 'Enfermera de investigación', 'en' => 'Research Nurse'],
                ['es' => 'Médico tratante', 'en' => 'Treating Physician'],
                ['es' => 'Farmacéutico', 'en' => 'Pharmacist'],
            ],
            CvTaxonomyTerm::TYPE_THERAPEUTIC_AREAS => [
                ['es' => 'Diabetes Mellitus tipo 2', 'en' => 'Type 2 Diabetes Mellitus'],
                ['es' => 'Hipertensión arterial sistémica', 'en' => 'Systemic Arterial Hypertension'],
                ['es' => 'Obesidad', 'en' => 'Obesity'],
                ['es' => 'Artritis reumatoide', 'en' => 'Rheumatoid Arthritis'],
                ['es' => 'Lupus eritematoso sistémico', 'en' => 'Systemic Lupus Erythematosus'],
                ['es' => 'Asma', 'en' => 'Asthma'],
                ['es' => 'EPOC', 'en' => 'COPD'],
                ['es' => 'Enfermedad renal crónica', 'en' => 'Chronic Kidney Disease'],
                ['es' => 'Vacunas', 'en' => 'Vaccines'],
                ['es' => 'Infectología', 'en' => 'Infectious Diseases'],
            ],
            CvTaxonomyTerm::TYPE_EDUCATION_DEGREES => [
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
            ],
        ];

        foreach ($seed as $type => $rows) {
            foreach ($rows as $index => $row) {
                \DB::table('cv_taxonomy_terms')->insert([
                    'taxonomy_type' => $type,
                    'name_es' => $row['es'],
                    'name_en' => $row['en'],
                    'sort_order' => $index + 1,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_taxonomy_terms');
    }
};
