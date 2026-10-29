<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'annonce_id',
        'avis_id',
        'sender_id',
        'receiver_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function annonce()
    {
        return $this->belongsTo(Annonce::class);
    }

    public function avis()
    {
        return $this->belongsTo(Avis::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class)->oldest();
    }
}
