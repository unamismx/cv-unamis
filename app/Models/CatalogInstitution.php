<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogInstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_type',
        'name',
        'state_name',
        'municipality_name',
        'city_name',
        'country_name',
        'external_source',
        'external_id',
        'active',
    ];
}
