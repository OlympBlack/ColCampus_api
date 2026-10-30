<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAnnonceMessageRequest;
use App\Models\Annonce;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Notifications\AnnouncementMessageNotification;
use App\Notifications\AvisMessageNotification;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnonceMessageController extends Controller
{
    public function index(Request $request, Annonce $annonce): JsonResponse
    {
        $sender = $request->user();
        $annonce->loadMissing(['user', 'medias']);

        if (!$annonce->user) {
            return response()->json([
                'message' => 'Annonce introuvable.',
            ], 404);
        }

        if ((int) $annonce->user_id === (int) $sender->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas discuter sur votre propre annonce.',
            ], 422);
        }

        $conversation = $this->resolveConversation($annonce, $sender->id);

        if (!$conversation) {
            $conversation = Conversation::create([
                'annonce_id' => $annonce->id,
                'sender_id' => $sender->id,
                'receiver_id' => $annonce->user_id,
                'last_message_at' => now(),
            ]);
        }

        $this->markMessagesAsRead($conversation, $sender->id);
        $conversation->loadMissing(['annonce.medias', 'sender', 'receiver', 'messages.user']);

        return response()->json([
            'data' => [
                'conversation' => ApiData::conversation($conversation),
                'messages' => ApiData::conversationMessages($conversation->messages),
            ],
        ]);
    }

    public function store(StoreAnnonceMessageRequest $request, Annonce $annonce): JsonResponse
    {
        $sender = $request->user();
        $annonce->loadMissing('user');

        if ((int) $annonce->user_id === (int) $sender->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas envoyer un message sur votre propre annonce.',
            ], 422);
        }

        if (!$annonce->user) {
            return response()->json([
                'message' => 'Annonce introuvable.',
            ], 404);
        }

        $conversation = $this->resolveConversation($annonce, $sender->id);

        if (!$conversation) {
            $conversation = Conversation::create([
                'annonce_id' => $annonce->id,
                'sender_id' => $sender->id,
                'receiver_id' => $annonce->user_id,
                'last_message_at' => now(),
            ]);
        }

        $message = $conversation->messages()->create([
            'user_id' => $sender->id,
            'content' => $request->validated('message'),
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'last_message_at' => $message->created_at,
        ])->save();

        $conversation->loadMissing(['sender', 'receiver']);
        $recipient = (int) $conversation->sender_id === (int) $sender->id ? $conversation->receiver : $conversation->sender;

        if ($recipient) {
            $recipient->notify(new AnnouncementMessageNotification($annonce, $sender, $conversation, $message));
        }

        broadcast(new MessageSent($conversation, $message))->toOthers();

        $conversation->loadMissing(['annonce', 'sender', 'receiver', 'messages.user']);

        return response()->json([
            'message' => 'Votre message a ete transmis au proprietaire de l annonce.',
            'data' => [
                'conversation' => ApiData::conversation($conversation),
                'messages' => ApiData::conversationMessages($conversation->messages),
                'message' => ApiData::conversationMessage($message),
            ],
        ]);
    }

    public function showConversation(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
            abort(403, 'Acces refuse.');
        }

        $this->markMessagesAsRead($conversation, $userId);
        $conversation->loadMissing(['annonce', 'sender', 'receiver', 'messages.user']);

        return response()->json([
            'data' => [
                'conversation' => ApiData::conversation($conversation),
                'messages' => ApiData::conversationMessages($conversation->messages),
            ],
        ]);
    }

    public function storeConversation(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'audio' => 'nullable|file',
            'duration' => 'nullable|numeric',
        ]);
        $sender = $request->user();

        if ($conversation->sender_id !== $sender->id && $conversation->receiver_id !== $sender->id) {
            abort(403, 'Acces refuse.');
        }

        $mediaUrl = null;
        $type = 'text';
        $duration = null;

        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('vocaux', 'public');
            $mediaUrl = asset('storage/' . $path);
            $type = 'audio';
            $duration = $request->input('duration') ? (int) round((float) $request->input('duration')) : null;
        }

        $content = $request->input('message') ?: ($type === 'audio' ? 'Message vocal' : '');
        if (empty($content) && !$mediaUrl) {
            return response()->json(['message' => 'Le message ne peut pas être vide.'], 422);
        }

        $message = $conversation->messages()->create([
            'user_id' => $sender->id,
            'type' => $type,
            'content' => $content,
            'media_url' => $mediaUrl,
            'duration' => $duration,
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'last_message_at' => $message->created_at,
        ])->save();

        $conversation->loadMissing(['annonce', 'avis', 'sender', 'receiver']);
        $recipient = (int) $conversation->sender_id === (int) $sender->id ? $conversation->receiver : $conversation->sender;

        if ($recipient && $conversation->annonce) {
            $recipient->notify(new AnnouncementMessageNotification($conversation->annonce, $sender, $conversation, $message));
        } elseif ($recipient && $conversation->avis) {
            $recipient->notify(new AvisMessageNotification($conversation->avis, $sender, $conversation, $message));
        }

        broadcast(new MessageSent($conversation, $message))->toOthers();

        return response()->json([
            'message' => 'Votre message a ete envoye.',
            'data' => [
                'conversation' => ApiData::conversation($conversation),
                'messages' => ApiData::conversationMessages($conversation->messages),
                'message' => ApiData::conversationMessage($message),
            ],
        ]);
    }

    public function markConversationAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
            abort(403, 'Acces refuse.');
        }

        $this->markMessagesAsRead($conversation, $userId);

        return response()->json(['message' => 'Messages marques comme lus.']);
    }

    private function resolveConversation(Annonce $annonce, int $userId): ?Conversation
    {
        return Conversation::query()
            ->where('annonce_id', $annonce->id)
            ->where(function ($query) use ($annonce, $userId) {
                $query->where(function ($query) use ($annonce, $userId) {
                    $query->where('sender_id', $userId)
                        ->where('receiver_id', $annonce->user_id);
                })->orWhere(function ($query) use ($annonce, $userId) {
                    $query->where('sender_id', $annonce->user_id)
                        ->where('receiver_id', $userId);
                });
            })
            ->first();
    }

    private function markMessagesAsRead(Conversation $conversation, int $userId): void
    {
        $updatedCount = ConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'updated_at' => now(),
            ]);

        // Marquer aussi comme lues les notifications relatives a cette conversation
        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->unreadNotifications->each(function ($notif) use ($conversation) {
                $meta = $notif->data['meta'] ?? [];
                $convId = $meta['conversation_id'] ?? null;
                if ($convId !== null && (int) $convId === (int) $conversation->id) {
                    $notif->markAsRead();
                }
            });
        }

        if ($updatedCount > 0) {
            broadcast(new MessagesRead($conversation, $userId))->toOthers();
        }
    }
}
