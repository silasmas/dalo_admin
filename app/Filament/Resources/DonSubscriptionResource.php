<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use App\Support\Sms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Don\DonDonation;
use App\Enums\SubscriptionCycle;
use App\Enums\SubscriptionState;
use Filament\Resources\Resource;
use App\Models\Don\DonSubscription;
use Illuminate\Support\Facades\Mail;
use App\Filament\Resources\DonSubscriptionResource\Pages;

class DonSubscriptionResource extends Resource
{
    protected static ?string $model = DonSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Souscriptions de dons';
    protected static ?string $navigationGroup = 'Dons';

    public static function form(Form $form): Form
    {
        return $form->schema([
           Forms\Components\Section::make('Infos principales')
    ->columns(2)
    ->schema([
        Forms\Components\Toggle::make('status')
            ->label('Active ?')
            ->default(true),

        Forms\Components\Select::make('user_id')
            ->label('Partenaire (user)')
            ->options(
                User::orderBy('firstname')->pluck('firstname', 'id')
            )
            ->searchable()
            ->required(),

        Forms\Components\TextInput::make('code')
            ->label('Code')
            ->required()
            ->maxLength(32),

        // 🔹 Type de souscription
        Forms\Components\Select::make('subscription_type_id')
            ->label('Type de souscription')
            ->relationship('donations', 'name')   // si tu as la relation dans le modèle
            ->options(
                DonDonation::orderBy('name')->pluck('name', 'id')
            )
            ->searchable()
            ->required()
            ->helperText('Ex : Partenaire mensuel, Paroissien, etc.'),

        // 🔹 Type de don (OBLIGATOIRE)
        Forms\Components\Select::make('donation_type_id')
            ->label('Type de don')
            ->relationship('donations', 'name')       // si tu as la relation
            ->options(
                DonDonation::orderBy('name')->pluck('name', 'id')
            )
            ->searchable()
            ->required()
            ->helperText('Ex : Dîme, Offrande, Construction, Média, etc.'),

        Forms\Components\TextInput::make('amount')
            ->label('Montant')
            ->numeric()
            ->required(),

        Forms\Components\TextInput::make('currency')
            ->label('Devise')
            ->maxLength(4)
            ->default('USD'),

        // Forms\Components\Select::make('state')
        //     ->label('État')
        //     ->options(collect(SubscriptionState::cases())
        //         ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
        //         ->toArray())
        //     ->required(),

        Forms\Components\Select::make('cycle')
            ->label('Cycle')
            ->options(collect(SubscriptionCycle::cases())
                ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
                ->toArray())
            ->required(),

        Forms\Components\Toggle::make('autopay')
            ->label('Prélèvement auto ?'),
    ]),


            Forms\Components\Section::make('Période')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Date de début')
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Date de fin')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('next_due_at')
                        ->label('Prochaine échéance')
                        ->seconds(false)
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('last_paid_at')
                        ->label('Dernier paiement')
                        ->seconds(false)
                        ->nullable(),
                ]),

            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Code')->searchable(),
                Tables\Columns\TextColumn::make('user.firstname')->label('Partenaire')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money('USD', true),
                Tables\Columns\BadgeColumn::make('state')
    ->label('État')
    ->formatStateUsing(function ($state) {
        // Si c’est déjà une enum, on renvoie directement son label
        if ($state instanceof SubscriptionState) {
            return $state->label();
        }

        // Sinon on essaie de la convertir depuis la valeur brute (string en DB)
        return SubscriptionState::tryFrom($state)?->label() ?? $state;
    }),
                Tables\Columns\TextColumn::make('end_date')->label('Fin')->date(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('notify')
                    ->label('Notifier (SMS & email)')
                    ->icon('heroicon-o-bell')
                    ->action(function (DonSubscription $record) {
                        $user = $record->user;

                        $message = sprintf(
                            "Shalom %s, ceci est un rappel concernant votre souscription de don (code %s). Merci pour votre soutien.",
                            $user?->firstname ?? $user?->name ?? 'cher partenaire',
                            $record->code
                        );

                        if ($user?->phone) {
                            Sms::send($user->phone, $message);
                        }

                        if ($user?->email) {
                            Mail::raw($message, function ($mail) use ($user) {
                                $mail->to($user->email)
                                    ->subject('Rappel souscription');
                            });
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDonSubscriptions::route('/'),
            'create' => Pages\CreateDonSubscription::route('/create'),
            'edit'   => Pages\EditDonSubscription::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        $count = DonSubscription::query()
            ->where('state', SubscriptionState::Active)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success'; // vert = actif
    }
}
