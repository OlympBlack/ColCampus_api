<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;

    protected $table = 'avis';

    protected $fillable = [
        'user_id',
        'universite_id',
        'quartier',
        'type_chambre',
        'nombre_places',
        'budget_min',
        'budget_max',
        'date_souhaitee',
        'description',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'nombre_places' => 'integer',
            'budget_min' => 'integer',
            'budget_max' => 'integer',
            'date_souhaitee' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function universite()
    {
        return $this->belongsTo(Universite::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
