<?php
namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Enums\UserStatus;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use App\Filament\Resources\MainUserResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MainUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon   = 'heroicon-o-user-group';
    protected static ?string $navigationGroup  = 'Administration';
    protected static ?string $modelLabel       = 'Utilisateur';
    protected static ?string $pluralModelLabel = 'Utilisateurs';
    protected static ?string $navigationLabel  = 'Utilisateurs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identité')
                ->description('Informations personnelles de base')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('firstname')
                        ->label('Prénom')
                        ->placeholder('Ex. Daniel')
                        ->helperText('Votre prénom tel qu’il apparaîtra dans l’admin.')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('lastname')
                        ->label('Nom')
                        ->placeholder('Ex. LOPEZ')
                        ->helperText('Votre nom de famille en majuscules si possible.')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('username')
                        ->label('Nom d’utilisateur')
                        ->placeholder('Ex. d.lopez')
                        ->helperText('Identifiant unique sans espaces. Utilisé pour la connexion si activé.')
                        ->maxLength(100)
                        ->unique(ignoreRecord: true, table: 'main_users', column: 'username'),

                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Date de naissance')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->helperText('Format jour/mois/année (ex. 25/12/1993).'),

                    Forms\Components\Select::make('gender')
                        ->label('Sexe')
                        ->options(['M' => 'Masculin', 'F' => 'Féminin'])
                        ->native(false)
                        ->searchable()
                        ->helperText('Optionnel. Sert aux stats et à la personnalisation.'),
                ]),

            Forms\Components\Section::make('Contact')
                ->columns(2)
                ->schema([
                   Forms\Components\TextInput::make('phone')
    ->label('Téléphone')
    ->tel()
    ->placeholder('Ex. +243 970 000 000')
    ->helperText('Numéro unique au format international recommandé.')
    ->required()
    ->maxLength(20)
    ->unique(
        ignoreRecord: true,
        table: 'main_users',
        column: 'phone',
    ),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->placeholder('Ex. admin@daloministries.org')
                        ->helperText('Adresse utilisée pour la récupération de compte et les notifications.')
                        ->maxLength(250)
                        ->unique(ignoreRecord: true, table: 'main_users', column: 'email'),

                    Forms\Components\TextInput::make('country')
                        ->label('Pays (code)')
                        ->placeholder('Ex. CD, FR, US…')
                        ->helperText('Code pays court (2–5 caractères).')
                        ->maxLength(5),

                    Forms\Components\TextInput::make('city')
                        ->label('Ville')
                        ->placeholder('Ex. Kinshasa')
                        ->helperText('Ville de résidence.')
                        ->maxLength(250),

                   Forms\Components\FileUpload::make('profile')
    ->label('Photo de profil')
    ->helperText('JPEG/PNG, 2 Mo max. Carré conseillé (512×512).')
    ->maxSize(2048)
    ->disk('s3') 
                            ->directory('publics/uploads/images/profiles')
                            ->visibility('private')
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096)
                            ->preserveFilenames(false)
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file) =>
                                    Str::ulid() . '.' . $file->getClientOriginalExtension()
                            )
    ->previewable()
    ->downloadable(),
                ]),

            Forms\Components\Section::make('Compte')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->native(false)
                        ->options(collect(UserStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all())
                        ->default(UserStatus::Pending->value)
                        ->required()
                        ->helperText('État du compte : Activé, En attente, Désactivé, etc.'),

                    // ⬇️ Remplacement: liste les rôles Filament Shield (guard admin)
                    Forms\Components\Select::make('default_role')
                        ->label('Rôle par défaut')
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->options(fn() => Role::query()
                                ->where('guard_name', config('filament.auth.guard', 'admin'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->helperText('Rôle initial (géré par Shield). Détermine les permissions par défaut.'),

                    Forms\Components\TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->placeholder('Saisir un mot de passe fort')
                        ->helperText('8+ caractères. Laisse vide en édition pour ne pas le changer.')
                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $operation) => $operation === 'create')
                        ->revealable(),

                    Forms\Components\TextInput::make('otp')
                        ->label('OTP (temporaire)')
                        ->placeholder('Ex. 834215')
                        ->helperText('Code de vérification à usage unique (si flux OTP activé).')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('public_token')
                        ->label('Token public')
                        ->placeholder('Généré par l’application')
                        ->helperText('Identifiant public pour les intégrations. À laisser vide.')
                        ->maxLength(1000),

                    Forms\Components\TextInput::make('ip_address')
                        ->label('Adresse IP')
                        ->placeholder('Ex. 192.168.1.10')
                        ->helperText('Dernière IP connue (remplie automatiquement si besoin).')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('fcm_token')
                        ->label('FCM token (mobile)')
                        ->placeholder('Généré par l’app mobile')
                        ->helperText('Pour notifications push. Laisser vide si non utilisé.')
                        ->maxLength(1000),

                    Forms\Components\TextInput::make('created_by')
                        ->label('Créé par (utilisateur)')
                        ->numeric()
                        ->default(0)
                        ->helperText('ID du créateur du compte. 0 = compte principal.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
              
                Tables\Columns\ImageColumn::make('profile')
                    ->label('Profile')
                     ->disk('s3')      // ⬅️ Filament va faire Storage::disk('s3')->url($state)
                ->size(64)->visibility('private')
                ->defaultImageUrl(asset('assets/images/avatar-default.png')),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->searchable(['firstname', 'lastname'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(function ($state) {
                        return \App\Enums\UserStatus::tryFrom((int) $state)?->label() ?? 'Inconnu';
                    })
                    ->color(function ($state) {
                        return match (\App\Enums\UserStatus::tryFrom((int) $state)) {
                            \App\Enums\UserStatus::Activated             => 'success',
                            \App\Enums\UserStatus::Pending               => 'warning',
                            \App\Enums\UserStatus::Disabled              => 'danger',
                            \App\Enums\UserStatus::Deleted               => 'gray',
                            \App\Enums\UserStatus::ValidatedAwaitingInfo => 'info',
                            \App\Enums\UserStatus::CreatingAwaitingOtp   => 'primary',
                            default                                      => 'gray',
                        };
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Sexe')
                    ->formatStateUsing(fn($state) => $state === 'M' ? 'M' : ($state === 'F' ? 'F' : '—'))
                    ->badge()
                    ->colors(['primary'])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country')
                    ->label('Pays')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_activity')
                    ->label('Dernière activité')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // 📅 Filtre de période de création
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                // 🎯 Statuts
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->multiple()
                    ->options(collect(UserStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all()),

                // 🧍 Sexe
                Tables\Filters\TernaryFilter::make('gender')
                    ->label('Sexe M / F / Tous')
                    ->nullable()
                    ->trueLabel('M')
                    ->falseLabel('F')
                    ->queries(
                        true: fn($q)  => $q->where('gender', 'M'),
                        false: fn($q) => $q->where('gender', 'F'),
                        blank: fn($q) => $q
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // soft delete
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMainUsers::route('/'),
            'create' => Pages\CreateMainUser::route('/create'),
            'view'   => Pages\ViewMainUser::route('/{record}'),
            'edit'   => Pages\EditMainUser::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\MainUserStats::class,
            \App\Filament\Widgets\MainUserMonthlyChart::class,
        ];
    }
}
