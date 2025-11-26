<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\DonationStatus;
use Illuminate\Support\Carbon;
use App\Models\Don\DonDonation;
use Filament\Resources\Resource;
use App\Filament\Resources\DonDonationResource\Pages;
use App\Filament\Resources\DonDonationResource\Widgets\DonDonationStats;

class DonDonationResource extends Resource
{
    protected static ?string $model = DonDonation::class;

    protected static ?string $navigationIcon  = 'heroicon-o-hand-raised';
    protected static ?string $navigationLabel = 'Dons';
    protected static ?string $navigationGroup = 'Dons & Partenaires';

    /** 🔢 Badge dans le menu : nombre de dons du mois courant */
    public static function getNavigationBadge(): ?string
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $count = DonDonation::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Infos donateur')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Partenaire (connecté)')
                        ->relationship('user', 'firstname') // adapte si fullname
                        ->searchable()
                        ->nullable(),

                    Forms\Components\TextInput::make('donor_name')
                        ->label('Nom affiché')
                        ->maxLength(191),

                    Forms\Components\TextInput::make('donor_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(191),

                    Forms\Components\TextInput::make('donor_phone')
                        ->label('Téléphone')
                        ->maxLength(50),
                ]),

            Forms\Components\Section::make('Détails du don')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('donation_type_id')
                        ->label('Type de don')
                        ->required()
                        ->options(
                            // Par ex. main_categories type = DONATION_TYPE
                            \App\Models\MainCategory::where('type', 'DONATION_TYPE')
                                ->pluck('cat_name', 'id')
                        )
                        ->searchable(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('currency')
                        ->label('Devise')
                        ->default('USD')
                        ->maxLength(4),

                    Forms\Components\Select::make('donation_status')
                        ->label('Statut du don')
                        ->options(collect(DonationStatus::cases())
                            ->mapWithKeys(fn($s) => [$s->value => $s->label()])
                        )
                        ->required(),

                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label('Payé le')
                        ->seconds(false),
                ]),

            Forms\Components\Section::make('Technique')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->label('Référence')
                        ->maxLength(64),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes internes')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réf.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Partenaire')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('USD', true),

                Tables\Columns\BadgeColumn::make('donation_status')
                    ->label('Statut')
                    ->formatStateUsing(fn($state) =>
                        $state instanceof DonationStatus ? $state->label() : $state
                    )
                    ->colors(fn($state) =>
                        $state instanceof DonationStatus ? [$state->color()] : []
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonDonations::route('/'),
            // 'view'  => Pages\ViewDonDonation::route('/{record}'),
            'edit'  => Pages\EditDonDonation::route('/{record}/edit'),
        ];
    }
      public static function getHeaderWidgets(): array
    {
        return [
            DonDonationStats::class,
        ];
    }
}
