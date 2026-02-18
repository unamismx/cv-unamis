<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvEducation extends Model
{
    use HasFactory;

    protected $table = 'cv_educations';

    protected $fillable = [
        'cv_localization_id',
        'institution_id',
        'institution_other',
        'start_date',
        'end_date',
        'start_year',
        'end_year',
        'is_ongoing',
        'year_completed',
        'completion_date',
        'degree_id',
        'degree_other',
        'license_number',
        'license_not_applicable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'completion_date' => 'date',
            'license_not_applicable' => 'boolean',
        ];
    }

    public function localization(): BelongsTo
    {
        return $this->belongsTo(CvLocalization::class, 'cv_localization_id');
    }
}
