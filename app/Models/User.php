<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'signature_file_path',
        'signature_signed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'signature_signed_at' => 'datetime',
        ];
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }

    public function signatureCaptureLinks(): HasMany
    {
        return $this->hasMany(SignatureCaptureLink::class);
    }

    public function cvSupportDocuments(): HasMany
    {
        return $this->hasMany(CvSupportDocument::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canManageCvTaxonomies(): bool
    {
        return mb_strtolower(trim((string) $this->email)) === 'jrivera@unamis.com.mx';
    }
}
