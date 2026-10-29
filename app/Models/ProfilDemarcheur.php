<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilDemarcheur extends Model
{
    use HasFactory;

    protected $table = 'profils_demarcheurs';

    protected $fillable = [
        'user_id',
        'ifu',
        'nom_agence',
        'zone_activite',
        'adresse_bureau',
        'ifu_doc_path',
        'piece_identite_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
