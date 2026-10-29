<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The event name to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Create a new event instance.
     */
    public function __construct(
        public \App\Models\Conversation $conversation,
        public \App\Models\ConversationMessage $message
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversations.' . $this->conversation->id),
            new PrivateChannel('users.' . $this->conversation->sender_id),
            new PrivateChannel('users.' . $this->conversation->receiver_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => \App\Support\ApiData::conversation($this->conversation->load(['annonce', 'sender', 'receiver'])),
            'message' => \App\Support\ApiData::conversationMessage($this->message->load('user')),
        ];
    }
}
