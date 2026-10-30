<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConversationMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_participants_can_send_text_and_audio_messages_even_if_unverified(): void
    {
        Storage::fake('public');

        $user1 = User::factory()->create([
            'prenoms' => 'Alice',
            'telephone' => '+22997111111',
            'role' => 'etudiant',
            'statut_verification' => 'en_attente', // not validated yet
        ]);

        $user2 = User::factory()->create([
            'prenoms' => 'Bob',
            'telephone' => '+22997222222',
            'role' => 'etudiant',
            'statut_verification' => 'non_rempli',
        ]);

        $conversation = Conversation::create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'last_message_at' => now(),
        ]);

        // Alice sends a text message
        Sanctum::actingAs($user1);
        $response = $this->postJson("/api/conversations/{$conversation->id}/messages", [
            'message' => 'Salut Bob !',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Votre message a ete envoye.')
            ->assertJsonPath('data.message.content', 'Salut Bob !')
            ->assertJsonPath('data.message.type', 'text');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
            'content' => 'Salut Bob !',
        ]);

        // Bob responds with an audio message
        Sanctum::actingAs($user2);
        $audioFile = UploadedFile::fake()->create('vocal.webm', 150, 'audio/webm');
        $audioResponse = $this->post("/api/conversations/{$conversation->id}/messages", [
            'audio' => $audioFile,
            'duration' => 12,
        ]);

        $audioResponse->assertOk()
            ->assertJsonPath('message', 'Votre message a ete envoye.')
            ->assertJsonPath('data.message.type', 'audio')
            ->assertJsonPath('data.message.duration', 12);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
            'duration' => 12,
        ]);

        // A third user cannot send messages to this conversation
        $user3 = User::factory()->create([
            'prenoms' => 'Intrus',
            'telephone' => '+22997333333',
            'role' => 'etudiant',
        ]);
        Sanctum::actingAs($user3);
        $forbiddenResponse = $this->postJson("/api/conversations/{$conversation->id}/messages", [
            'message' => 'Je m incruste',
        ]);
        $forbiddenResponse->assertForbidden();
    }
}
