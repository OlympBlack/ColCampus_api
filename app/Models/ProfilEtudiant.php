<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilEtudiant extends Model
{
    use HasFactory;

    protected $table = 'profils_etudiants';

    protected $fillable = [
        'user_id',
        'etablissement',
        'universite_id',
        'filiere',
        'niveau',
        'sexe',
        'preferences',
        'budget_max',
        'carte_etudiant_path',
        'cip_path'
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function universite()
    {
        return $this->belongsTo(Universite::class);
    }
}
