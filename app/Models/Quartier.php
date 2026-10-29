<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    protected $fillable = [
        'universite_id',
        'nom',
        'arrondissement',
        'source',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function universite()
    {
        return $this->belongsTo(Universite::class);
    }
}
