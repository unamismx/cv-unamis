<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'institutional_address',
        'last_published_at',
    ];

    protected function casts(): array
    {
        return [
            'last_published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function localizations(): HasMany
    {
        return $this->hasMany(CvLocalization::class);
    }

    public function localization(string $locale): ?CvLocalization
    {
        return $this->localizations->firstWhere('locale', $locale);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CvVersion::class)->orderByDesc('version_number');
    }
}
