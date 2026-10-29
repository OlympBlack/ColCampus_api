<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function broadcastAs(): string
    {
        return 'MessagesRead';
    }

    public function __construct(
        public \App\Models\Conversation $conversation,
        public int $readerId
    ) {
    }

    public function broadcastOn(): array
    {
        // Broadcast to the OTHER user in the conversation
        $otherUserId = $this->conversation->sender_id === $this->readerId 
            ? $this->conversation->receiver_id 
            : $this->conversation->sender_id;

        return [
            new PrivateChannel('users.' . $otherUserId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'reader_id' => $this->readerId,
        ];
    }
}
