<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvSupportDocument extends Model
{
    use HasFactory;

    public const CATEGORY_GCP = 'gcp_certificate';
    public const CATEGORY_LICENSE = 'professional_license';
    public const CATEGORY_TITLE = 'professional_title';
    public const CATEGORY_TRAINING = 'training_certificate';
    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'category',
        'title',
        'original_name',
        'file_path',
        'file_size_bytes',
        'mime_type',
    ];

    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_GCP => 'Certificado GCP',
            self::CATEGORY_LICENSE => 'Cédula profesional',
            self::CATEGORY_TITLE => 'Título profesional',
            self::CATEGORY_TRAINING => 'Certificados de cursos / entrenamientos',
            self::CATEGORY_OTHER => 'Otro documento',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

