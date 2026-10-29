<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Universite extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'ville',
        'departement',
        'commune',
        'accent',
        'actif',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'accent' => 'boolean',
            'actif' => 'boolean',
            'ordre' => 'integer',
        ];
    }

    public function quartiers()
    {
        return $this->hasMany(Quartier::class);
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class);
    }
}
