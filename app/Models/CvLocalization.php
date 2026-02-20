<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CvLocalization extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'locale',
        'title_name',
        'office_phone',
        'fax_number',
        'email',
        'profession_label',
        'position_label',
        'summary_text',
        'professional_experience_json',
        'clinical_research_json',
        'trainings_json',
    ];

    protected function casts(): array
    {
        return [
            'professional_experience_json' => 'array',
            'clinical_research_json' => 'array',
            'trainings_json' => 'array',
        ];
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CvEducation::class, 'cv_localization_id')->orderBy('sort_order');
    }

    public function gcpCertifications(): HasMany
    {
        return $this->hasMany(CvGcpCertification::class, 'cv_localization_id')->orderBy('sort_order');
    }
}
