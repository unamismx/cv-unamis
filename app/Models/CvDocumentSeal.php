<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvDocumentSeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'year',
        'sequence',
        'cv_id',
        'locale',
        'hash_sha256',
        'signature_hmac',
        'signed_at',
        'signer_email',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }
}
