<?php
namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use App\Models\Appt\Appointment;
use Filament\Resources\Resource;
use App\Models\Appt\Availability;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table; // adapte le namespace
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\GalImageResource\Widgets\GalleryStats;
use App\Filament\Resources\AppointmentResource\Widgets\AppointmentStats;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationGroup = 'Rendez-vous';
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Rendez-vous';
    protected static ?string $modelLabel      = 'Rendez-vous';

    public static function form(Form $form): Form
    {
        $statuses = [
            'pending'   => 'En attente',
            'confirmed' => 'Confirmé',
            'canceled'  => 'Annulé',
            'done'      => 'Terminé',
            'no_show'   => 'Absent',
        ];

        $weekdayLabels = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];

        return $form
            ->schema([
                // INFOS PRINCIPALES
                Forms\Components\Section::make('Infos principales')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('titre')
                            ->label('Titre du rendez-vous')
                            ->required()
                            ->maxLength(200)
                            ->helperText('Ex : Entretien pastoral, prière, conseil…'),

                        Forms\Components\Select::make('app_status')
                            ->label('Statut du RDV')
                            ->options($statuses)
                            ->required()
                            ->helperText('Vous pourrez modifier le statut après le rendez-vous.'),
                    ]),

                // PARTICIPANTS
                Forms\Components\Section::make('Participants')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Client')
                            ->options(
                                User::where('default_role', 5)
                                    ->select('id', 'firstname', 'lastname')
                                    ->get()
                                    ->mapWithKeys(fn($u) => [
                                        $u->id => trim($u->firstname . ' ' . $u->lastname),
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->helperText('Seuls les utilisateurs avec le rôle client (default_role = 5) apparaissent ici.'),

                        Forms\Components\Select::make('staff_user_id')
                            ->label('Pasteur / Staff')
                          ->options(
                                User::where('default_role', 2)
                                    ->select('id', 'firstname', 'lastname')
                                    ->get()
                                    ->mapWithKeys(fn($u) => [
                                        $u->id => trim($u->firstname . ' ' . $u->lastname),
                                    ])
                            )
                            ->searchable()
                            ->nullable()
                            ->reactive()
                            ->helperText('Sélectionnez le serviteur qui recevra le rendez-vous.'),

                        Forms\Components\TextInput::make('provider_name')
                            ->label('Nom du serviteur (si pas dans le système)')
                            ->maxLength(191)
                            ->helperText('À utiliser si le serviteur n’a pas de compte utilisateur.'),
                    ]),

                // JOUR & CRÉNEAU (logique dynamique)
                Forms\Components\Section::make('Jour & créneau')
                    ->columns(2)
                    ->schema([
                        // 1) Sélection du jour (30 prochains jours)
                        Forms\Components\Select::make('appointment_date')
                            ->label('Jour du rendez-vous')
                            ->dehydrated(false)
                            ->options(function () {
                                $options = [];
                                $today   = now()->startOfDay();

                                for ($i = 0; $i < 30; $i++) {
                                    $date = $today->copy()->addDays($i);
                                    // valeur = date (pour scheduled_at / end_at)
                                    // label = Jour de la semaine + date
                                    $options[$date->toDateString()] = $date->translatedFormat('l d/m/Y');
                                    // ex : "Lundi 18/11/2025"
                                }

                                return $options;
                            })
                            ->disabled(fn(Get $get) => ! $get('staff_user_id'))
                            ->reactive()
                            ->helperText('Choisissez d’abord un pasteur, puis un jour dans les 30 prochains jours.'),

                        // 2) Créneaux horaires en fonction du jour de la semaine
                        Forms\Components\Select::make('time_slot')
                            ->label('Créneau horaire')
                            ->dehydrated(false)
                            ->options(function (Get $get) use ($weekdayLabels) {
    $staffId = $get('staff_user_id');
    $dateStr = $get('appointment_date');

    if (! $staffId || ! $dateStr) {
        return [];
    }

    $date = Carbon::parse($dateStr)->startOfDay();
    $weekday = (int) $date->dayOfWeekIso; // 1=lundi, 7=dimanche

    // 1) Dispos théoriques du pasteur (appt_availabilities)
    $availabilities = Availability::query()
        ->where('staff_user_id', $staffId)
        ->where('status', 1)
        ->where('weekday', $weekday)
        ->get();

    if ($availabilities->isEmpty()) {
        return [];
    }

    // 2) RDV déjà pris ce jour-là (ex: pending, confirmed)
    $takenAppointments = Appointment::query()
        ->where('staff_user_id', $staffId)
        ->whereDate('scheduled_at', $dateStr)
        ->whereIn('app_status', ['pending', 'confirmed']) // tu peux ajuster la liste
        ->get(['scheduled_at', 'end_at']);

    // On convertit les RDV en intervalles de temps (Carbon)
    $takenIntervals = $takenAppointments->map(fn ($appt) => [
        'start' => Carbon::parse($appt->scheduled_at),
        'end'   => Carbon::parse($appt->end_at),
    ]);

    $slots = [];

    foreach ($availabilities as $a) {
        $slotDuration = (int) $a->slot_duration;

        // On reconstruit les horaires réels du jour
        $start = Carbon::parse(
            $date->toDateString() . ' ' . Carbon::parse($a->start_time)->toTimeString()
        );
        $end = Carbon::parse(
            $date->toDateString() . ' ' . Carbon::parse($a->end_time)->toTimeString()
        );

        while ($start->lt($end)) {
            $slotEnd = $start->copy()->addMinutes($slotDuration);
            if ($slotEnd->gt($end)) {
                break;
            }

            // 3) Vérifier si ce créneau se chevauche avec un RDV déjà pris
            $overlaps = $takenIntervals->contains(function ($interval) use ($start, $slotEnd) {
                // il y a chevauchement si start < finRDV ET slotEnd > débutRDV
                return $start->lt($interval['end']) && $slotEnd->gt($interval['start']);
            });

            if (! $overlaps) {
                // valeur stockée : "HH:MM|HH:MM"
                $value = $start->format('H:i') . '|' . $slotEnd->format('H:i');
                $label = $start->format('H:i') . ' - ' . $slotEnd->format('H:i');

                $slots[$value] = $label;
            }

            $start = $slotEnd;
        }
    }

    return $slots;
})

                            // ->options(function (Get $get) use ($weekdayLabels) {
                            //     $staffId = $get('staff_user_id');
                            //     $dateStr = $get('appointment_date');

                            //     if (! $staffId || ! $dateStr) {
                            //         return [];
                            //     }

                            //     // On récupère la date choisie...
                            //     $date = Carbon::parse($dateStr)->startOfDay();
                            //     // ...puis on en dérive le JOUR DE LA SEMAINE (1 = lundi, 7 = dimanche)
                            //     $weekday = (int) $date->dayOfWeekIso;

                            //     // ⬇️ ICI on utilise bien le jour de la semaine pour interroger les dispos
                            //     $availabilities = Availability::query()
                            //         ->where('staff_user_id', $staffId)
                            //         ->where('status', 1)
                            //         ->where('weekday', $weekday)
                            //         ->get();

                            //     if ($availabilities->isEmpty()) {
                            //         // aucun créneau ce jour-là
                            //         return [];
                            //     }

                            //     $slots = [];

                            //     foreach ($availabilities as $a) {
                            //         $slotDuration = (int) $a->slot_duration;

                            //         $start = Carbon::parse(
                            //             $date->toDateString() . ' ' . Carbon::parse($a->start_time)->toTimeString()
                            //         );

                            //         $end = Carbon::parse(
                            //             $date->toDateString() . ' ' . Carbon::parse($a->end_time)->toTimeString()
                            //         );

                            //         while ($start->lt($end)) {
                            //             $slotEnd = $start->copy()->addMinutes($slotDuration);
                            //             if ($slotEnd->gt($end)) {
                            //                 break;
                            //             }

                            //             // valeur stockée : "HH:MM|HH:MM"
                            //             $value = $start->format('H:i') . '|' . $slotEnd->format('H:i');
                            //             $label = $start->format('H:i') . ' - ' . $slotEnd->format('H:i');

                            //             $slots[$value] = $label;

                            //             $start = $slotEnd;
                            //         }
                            //     }

                            //     return $slots;
                            // })
                            ->reactive()
                            ->disabled(fn(Get $get) => ! $get('appointment_date') || ! $get('staff_user_id'))
                            ->helperText(function (Get $get) use ($weekdayLabels) {
                                $staffId = $get('staff_user_id');
                                $dateStr = $get('appointment_date');

                                if (! $staffId) {
                                    return 'Sélectionnez d’abord un pasteur.';
                                }

                                if (! $dateStr) {
                                    return 'Choisissez un jour pour afficher les créneaux disponibles.';
                                }

                                $date    = Carbon::parse($dateStr)->startOfDay();
                                $weekday = (int) $date->dayOfWeekIso;

                                // Encore ici : on vérifie la dispo via le jour de semaine
                                $hasAvail = Availability::query()
                                    ->where('staff_user_id', $staffId)
                                    ->where('status', 1)
                                    ->where('weekday', $weekday)
                                    ->exists();

                                if (! $hasAvail) {
                                    return 'Ce pasteur n’est pas disponible le ' . ($weekdayLabels[$weekday] ?? 'jour sélectionné') . '.';
                                }

                                return 'Choisissez l’heure de passage parmi les créneaux disponibles.';
                            })->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            // Si aucun créneau sélectionné => on reset
                            if (! $state) {
                                $set('scheduled_at', null);
                                $set('end_at', null);
                                return;
                            }

                            $dateStr = $get('appointment_date');
                            if (! $dateStr) {
                                return;
                            }

                            // $state arrive sous forme "HH:MM|HH:MM"
                            if (! str_contains($state, '|')) {
                                return;
                            }

                            [$startTime, $endTime] = explode('|', $state, 2);

                            // On construit proprement les datetime
                            $start = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $startTime);
                            $end   = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $endTime);

                                                                              // On pousse des strings bien lisibles pour le DateTimePicker
                            $set('scheduled_at', $start->toDateTimeString()); // "2025-11-24 08:00:00"
                            $set('end_at', $end->toDateTimeString());
                        }),

                        // Ces champs sont remplis automatiquement, pas modifiables à la main
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Début')
                            ->seconds(false)
                            ->reactive() // ⚠ pour que l’UI suive les changements de state
                            ->readOnly() // au lieu de disabled(), l’input reste envoyé côté Livewire
                            ->helperText('Rempli automatiquement à partir du créneau sélectionné.'),

                        Forms\Components\DateTimePicker::make('end_at')
                            ->label('Fin')
                            ->seconds(false)
                            ->reactive()
                            ->readOnly()
                            ->helperText('Rempli automatiquement à partir du créneau sélectionné.'),
                    ]),

                // DÉTAILS
                Forms\Components\Section::make('Détails')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->helperText('Brève description de la raison du rendez-vous.'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes internes')
                            ->rows(3)
                            ->helperText('Visible uniquement par l’équipe, non affiché au client.'),
                    ]),

                // ANNULATION
                Forms\Components\Section::make('Annulation')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('cancel_reason')
                            ->label('Raison d’annulation'),

                        Forms\Components\DateTimePicker::make('canceled_at')
                            ->label('Date d’annulation')
                            ->seconds(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $statuses = [
            'pending'   => 'En attente',
            'confirmed' => 'Confirmé',
            'canceled'  => 'Annulé',
            'done'      => 'Terminé',
            'no_show'   => 'Absent',
        ];

        return $table
            ->columns([
                ImageColumn::make('user.profile')
                    ->label('Photo')
                    ->disk('s3')
                    ->size(64)->visibility('private')
                    ->defaultImageUrl(asset('assets/images/avatar-default.png'))
                    ->size(60)
                    ->square(),
                Tables\Columns\TextColumn::make('user.fullName')
                    ->label('Fidèle')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('staff.profile')
                    ->label('Photo')
                    ->disk('s3')
                    ->size(64)->visibility('private')
                    ->defaultImageUrl(asset('assets/images/avatar-default.png'))
                    ->size(60)
                    ->square(),
                Tables\Columns\TextColumn::make('staff.fullName')
                    ->label('Pasteur / Staff')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('app_status')
                    ->label('Statut')
                    ->formatStateUsing(fn($state) => $statuses[$state] ?? $state)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger'  => 'canceled',
                        'info'    => 'done',
                        'gray'    => 'no_show',
                    ]),
                // ⭐ SelectColumn pour changer le statut directement dans le tableau
                // Affichage lisible du statut
                Tables\Columns\BadgeColumn::make('app_status')
                    ->label('Statut')
                    ->formatStateUsing(fn(string $state) => [
                        'pending'   => 'En attente',
                        'confirmed' => 'Confirmé',
                        'canceled'  => 'Annulé',
                        'done'      => 'Terminé',
                        'no_show'   => 'Absent',
                    ][$state] ?? $state)
                    ->colors([
                        'gray'    => 'pending',
                        'success' => 'confirmed',
                        'danger'  => 'canceled',
                        'warning' => 'no_show',
                        'info'    => 'done',
                    ]),

                // Select pour modifier les statuts courants
                SelectColumn::make('app_status')
                    ->label('Changer le statut')
                    ->options($statuses)
                    ->selectablePlaceholder(false)
                // si RDV terminé => on ne peut plus toucher au statut
                    ->disabled(fn(Appointment $record) => $record->app_status === 'done')
                    ->afterStateUpdated(function ($state, Appointment $record) {
                        if ($state === 'confirmed') {
                            if (! $record->user?->phone) {
                                Notification::make()
                                    ->title('SMS non envoyé')
                                    ->body('Le client n’a pas de numéro de téléphone enregistré.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            Notification::make()
                                ->title('RDV confirmé')
                                ->body('Le statut a été mis à jour. Un SMS de confirmation est envoyé au client.')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Actif ?')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('app_status')
                    ->label('Statut')
                    ->options($statuses),

                Tables\Filters\Filter::make('upcoming')
                    ->label('À venir')
                    ->query(fn($q) => $q->where('scheduled_at', '>=', now())),
            ])
            ->actions([
                // Action pour marquer le RDV comme terminé
                Tables\Actions\Action::make('markDone')
                    ->label('Marquer terminé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Appointment $record) => $record->app_status !== 'done')
                    ->requiresConfirmation() // ⬅️ popup de confirmation Filament
                    ->modalHeading('Confirmer la fin du rendez-vous')
                    ->modalDescription('Êtes-vous sûr de vouloir marquer ce rendez-vous comme terminé ? Le statut ne pourra plus être modifié.')
                    ->modalSubmitActionLabel('Oui, marquer terminé')
                    ->action(function (Appointment $record) {
                        $record->update(['app_status' => 'done']);

                        Notification::make()
                            ->title('Rendez-vous terminé')
                            ->body('Le rendez-vous a été marqué comme terminé. Le statut est maintenant verrouillé.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit'   => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $now = Carbon::now();

        $count = Appointment::query()
            ->where('scheduled_at', '>=', $now)
            ->whereIn('app_status', ['pending', 'confirmed'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    public static function getHeaderWidgets(): array
    {
        return [
            AppointmentStats::class,
        ];
    }
}
