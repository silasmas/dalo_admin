<?php
namespace App\Observers;
use Illuminate\Support\Carbon;
use App\Support\Sms;
use App\Models\Appt\Appointment;

class AppointmentObserver
{
    public function updated(Appointment $appointment): void
    {
        // On ne réagit que si le statut vient de changer ET qu'il est confirmé
        if (! $appointment->wasChanged('app_status') || $appointment->app_status !== 'confirmed') {
            return;
        }

        // On récupère le client
        $user = $appointment->user;
        if (! $user?->phone) {
            return; // pas de téléphone => rien à envoyer
        }

        // Format de la date
        $date = $appointment->scheduled_at
            ? Carbon::parse($appointment->scheduled_at)->format('d/m/Y à H:i')
            : 'prochainement';

        // Nom du pasteur (staff ou provider_name)
        $pastorName = $appointment->staff
            ? trim(($appointment->staff->firstname ?? '') . ' ' . ($appointment->staff->lastname ?? ''))
            : ($appointment->provider_name ?: 'le pasteur');

        // Nom du fidèle
        $userName = $user->firstname
            ?? $user->lastname
            ?? 'bien-aimé(e)';

        // ✅ Message court + pasteur
        $message = sprintf(
            'Shalom %s, votre rendez-vous avec %s le %s est confirmé.',
            $userName,
            $pastorName,
            $date
        );

        try {
            Sms::send($user->phone, $message);
        } catch (\Throwable $e) {
            \Log::warning('Erreur envoi SMS confirmation RDV', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
