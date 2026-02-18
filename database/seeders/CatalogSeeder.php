<?php

namespace Database\Seeders;

use App\Models\CatalogDegree;
use App\Models\CatalogInstitution;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = [
            ['institution_type' => 'universidad', 'name' => 'Universidad Nacional Autónoma de México (UNAM)', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'universidad', 'name' => 'Instituto Politécnico Nacional (IPN)', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'universidad', 'name' => 'Universidad Autónoma de Yucatán (UADY)', 'state_name' => 'Yucatán'],
            ['institution_type' => 'universidad', 'name' => 'Universidad Anáhuac México', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'universidad', 'name' => 'Universidad de Guadalajara (UDG)', 'state_name' => 'Jalisco'],
            ['institution_type' => 'hospital', 'name' => 'Hospital General de México "Dr. Eduardo Liceaga"', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'hospital', 'name' => 'Instituto Nacional de Ciencias Médicas y Nutrición Salvador Zubirán', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'hospital', 'name' => 'Hospital Regional de Alta Especialidad de la Península de Yucatán', 'state_name' => 'Yucatán'],
            ['institution_type' => 'hospital', 'name' => 'Hospital General Agustín O\'Horán', 'state_name' => 'Yucatán'],
            ['institution_type' => 'bachillerato', 'name' => 'Escuela Nacional Preparatoria (ENP)', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'bachillerato', 'name' => 'Colegio de Ciencias y Humanidades (CCH)', 'state_name' => 'Ciudad de México'],
            ['institution_type' => 'bachillerato', 'name' => 'COBAY', 'state_name' => 'Yucatán'],
            ['institution_type' => 'tecnica', 'name' => 'CONALEP', 'state_name' => null],
            ['institution_type' => 'tecnica', 'name' => 'CBTIS', 'state_name' => null],
        ];

        foreach ($institutions as $row) {
            CatalogInstitution::updateOrCreate(
                ['name' => $row['name']],
                [
                    'institution_type' => $row['institution_type'],
                    'state_name' => $row['state_name'],
                    'country_name' => 'México',
                    'active' => true,
                ]
            );
        }

        $degrees = [
            ['degree_type' => 'especialidad_medica', 'name_es' => 'Medicina Interna', 'name_en' => 'Internal Medicine'],
            ['degree_type' => 'especialidad_medica', 'name_es' => 'Pediatría', 'name_en' => 'Pediatrics'],
            ['degree_type' => 'especialidad_medica', 'name_es' => 'Cardiología', 'name_en' => 'Cardiology'],
            ['degree_type' => 'especialidad_medica', 'name_es' => 'Infectología', 'name_en' => 'Infectious Diseases'],
            ['degree_type' => 'carrera_salud', 'name_es' => 'Médico Cirujano', 'name_en' => 'MD'],
            ['degree_type' => 'carrera_salud', 'name_es' => 'Enfermería', 'name_en' => 'Nursing'],
            ['degree_type' => 'carrera_salud', 'name_es' => 'Nutrición', 'name_en' => 'Nutrition'],
            ['degree_type' => 'carrera_salud', 'name_es' => 'Químico Farmacéutico Biólogo', 'name_en' => 'Pharmaceutical Chemist Biologist'],
            ['degree_type' => 'carrera_tecnica', 'name_es' => 'Técnico Laboratorista Clínico', 'name_en' => 'Clinical Laboratory Technician'],
            ['degree_type' => 'carrera_tecnica', 'name_es' => 'Técnico en Enfermería', 'name_en' => 'Nursing Technician'],
            ['degree_type' => 'bachillerato', 'name_es' => 'Bachillerato General', 'name_en' => 'General High School Diploma'],
        ];

        foreach ($degrees as $row) {
            CatalogDegree::updateOrCreate(
                ['name_es' => $row['name_es']],
                [
                    'degree_type' => $row['degree_type'],
                    'name_en' => $row['name_en'],
                    'active' => true,
                ]
            );
        }
    }
}
