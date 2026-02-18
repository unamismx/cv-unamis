<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvGcpCertification extends Model
{
    use HasFactory;

    protected $table = 'cv_gcp_certifications';

    protected $fillable = [
        'cv_localization_id',
        'provider',
        'course_name',
        'guideline_version',
        'certificate_language',
        'participant_name',
        'certificate_id',
        'issued_at',
        'expires_at',
        'no_expiration',
        'status',
        'verification_url',
        'certificate_file_path',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'no_expiration' => 'boolean',
        ];
    }

    public function localization(): BelongsTo
    {
        return $this->belongsTo(CvLocalization::class, 'cv_localization_id');
    }
}
