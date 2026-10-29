<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversations.{id}', function ($user, $id) {
    return (int) $user->id === (int) \App\Models\Conversation::find($id)?->sender_id ||
        (int) $user->id === (int) \App\Models\Conversation::find($id)?->receiver_id;
});

Broadcast::channel('online', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
