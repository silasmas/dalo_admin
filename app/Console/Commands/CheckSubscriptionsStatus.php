<?php

// app/Console/Commands/CheckSubscriptionsStatus.php
namespace App\Console\Commands;

use App\Enums\SubscriptionState;
use App\Models\Don\DonSubscription;
use App\Support\Sms; // si tu as déjà un helper Sms
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckSubscriptionsStatus extends Command
{
    protected $signature = 'don:check-subscriptions';
    protected $description = 'Vérifie les souscriptions, envoie les rappels J-5 et met à jour les états.';

    public function handle(): int
    {
        $this->info('▶ Vérification des souscriptions...');

        $this->sendFiveDaysReminders();
        $this->closeExpiredSubscriptions();

        $this->info('✅ Terminé.');
        return Command::SUCCESS;
    }

    protected function sendFiveDaysReminders(): void
    {
        $targetDate = now()->addDays(5)->toDateString();

        $subs = DonSubscription::query()
            ->where('state', SubscriptionState::Active)
            ->whereNotNull('end_date')
            ->whereDate('end_date', $targetDate)
            ->whereNull('reminder_5d_sent_at')
            ->with('user')
            ->get();

        foreach ($subs as $sub) {
            $user = $sub->user;

            $message = sprintf(
                "Shalom %s, votre souscription de don (code %s) arrive à échéance le %s. Merci pour votre fidélité.",
                $user?->firstname ?? $user?->name ?? 'cher partenaire',
                $sub->code,
                $sub->end_date?->format('d/m/Y')
            );

            // SMS
            try {
                if ($user?->phone) {
                    // Sms::send($user->phone, $message);
                }
            } catch (\Throwable $e) {
                Log::warning('Erreur envoi SMS souscription J-5', [
                    'subscription_id' => $sub->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Email
            try {
                if ($user?->email) {
                    Mail::raw($message, function ($mail) use ($user) {
                        $mail->to($user->email)
                            ->subject('Rappel : votre souscription arrive à échéance');
                    });
                }
            } catch (\Throwable $e) {
                Log::warning('Erreur envoi email souscription J-5', [
                    'subscription_id' => $sub->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $sub->update(['reminder_5d_sent_at' => now()]);
        }
    }

    protected function closeExpiredSubscriptions(): void
    {
        $today = now()->toDateString();

        $subs = DonSubscription::query()
            ->where('state', SubscriptionState::Active)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($subs as $sub) {
            $sub->update(['state' => SubscriptionState::Completed]);
        }
    }
}
