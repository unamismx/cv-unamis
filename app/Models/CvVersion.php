<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'version_number',
        'snapshot_json',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_json' => 'array',
        ];
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}
