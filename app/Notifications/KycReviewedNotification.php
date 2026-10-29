<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KycReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $status,
        private readonly ?string $comment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'notification' => $this->toArray($notifiable)
        ]);
    }

    public function toArray(object $notifiable): array
    {
        $isApproved = $this->status === 'valide';

        return [
            'type' => 'kyc_reviewed',
            'title' => $isApproved ? 'Document valide par l administration' : 'Document KYC rejete',
            'body' => $isApproved
                ? 'Votre dossier KYC a ete valide. Vous pouvez maintenant publier et contacter librement.'
                : ($this->comment ?: 'Votre dossier KYC a ete rejete. Consultez le commentaire admin pour corriger votre dossier.'),
            'action_url' => '#/dashboard/' . $notifiable->role,
            'action_label' => 'Ouvrir le dashboard',
            'meta' => [
                'statut_verification' => $this->status,
                'commentaire_admin' => $this->comment,
            ],
        ];
    }
}