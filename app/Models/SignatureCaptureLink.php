<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SignatureCaptureLink extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'token_hash',
        'expires_at',
        'used_at',
        'sent_at',
        'sent_error',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
