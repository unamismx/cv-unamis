<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogDegree extends Model
{
    use HasFactory;

    protected $fillable = [
        'degree_type',
        'name_es',
        'name_en',
        'external_source',
        'external_id',
        'active',
    ];
}
