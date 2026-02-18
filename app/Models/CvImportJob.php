<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_file_name',
        'stored_file_path',
        'detected_locale',
        'confidence_score',
        'parse_status',
        'notes',
    ];
}
