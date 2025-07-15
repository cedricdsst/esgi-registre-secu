<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AdminUserCreated extends Notification
{
    use Queueable;

    protected $adminName;
    protected $temporaryPassword;

    public function __construct($token, $adminName, $temporaryPassword)
    {
        // $token non utilisé mais gardé pour compatibilité
        $this->adminName = $adminName;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // URL vers la page de connexion du frontend
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $frontendUrl . '/login';

        return (new MailMessage)
            ->subject('Bienvenue ! Votre compte a été créé')
            ->greeting('Bonjour ' . $notifiable->prenom . ' ' . $notifiable->nom . ',')
            ->line('Votre compte utilisateur a été créé par ' . $this->adminName . ' sur notre plateforme de registre de sécurité.')
            ->line('**Vos informations de connexion :**')
            ->line('• Email : ' . $notifiable->email)
            ->line('• Mot de passe temporaire : **' . $this->temporaryPassword . '**')
            ->line('Vous pouvez maintenant vous connecter avec ces informations.')
            ->action('Finaliser mon inscription', $url)
            ->line('Ce lien expirera dans 7 jours.')
            ->line('⚠️ **Important** : Changez votre mot de passe temporaire dès votre première connexion.')
            ->line('Si vous n\'avez pas demandé la création de ce compte, veuillez ignorer cet email.')
            ->salutation('Cordialement,')
            ->salutation('L\'équipe AXIGNIS');
    }

    public function toArray($notifiable)
    {
        return [
            'admin_name' => $this->adminName,
            'temporary_password' => $this->temporaryPassword,
        ];
    }
} 