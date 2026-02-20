<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvTaxonomyTerm extends Model
{
    use HasFactory;

    public const TYPE_PROFESSIONS = 'professions';
    public const TYPE_STUDY_POSITIONS = 'study_positions';
    public const TYPE_STUDY_ROLES = 'study_roles';
    public const TYPE_THERAPEUTIC_AREAS = 'therapeutic_areas';

    protected $fillable = [
        'taxonomy_type',
        'name_es',
        'name_en',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    public static function availableTypes(): array
    {
        return [
            self::TYPE_PROFESSIONS,
            self::TYPE_STUDY_POSITIONS,
            self::TYPE_STUDY_ROLES,
            self::TYPE_THERAPEUTIC_AREAS,
        ];
    }
}
