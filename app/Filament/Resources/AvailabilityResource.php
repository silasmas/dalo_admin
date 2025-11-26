<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Appt\Availability;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use App\Filament\Resources\AvailabilityResource\Pages;

class AvailabilityResource extends Resource
{
    protected static ?string $model = Availability::class;

    protected static ?string $navigationGroup = 'Rendez-vous';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Disponibilités';

    public static function form(Form $form): Form
    {
        $weekdays = [
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
        Forms\Components\Section::make('Infos principales')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
 Hidden::make('created_by')->default(Auth::id()),
                        Forms\Components\Select::make('weekday')
                            ->label('Jour de la semaine')
                            ->options($weekdays)
                            ->required()
                            ->helperText('Choisissez le jour où ce créneau de rendez-vous sera disponible.'),

                        Forms\Components\Select::make('staff_user_id')
                            ->label('Pasteur / Staff')
                            ->relationship('staff', 'firstname')
                            ->searchable()
                            ->nullable()
                            ->helperText('Sélectionnez le pasteur ou membre du staff concerné par ce créneau (facultatif).'),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Heure de début')
                            ->required()
                            ->helperText('Heure à laquelle commence ce créneau de rendez-vous.'),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Heure de fin')
                            ->required()
                            ->helperText('Heure à laquelle se termine ce créneau de rendez-vous.'),

                        Forms\Components\TextInput::make('slot_duration')
                            ->label('Durée d’un RDV (min)')
                            ->numeric()
                            ->required()
                            ->helperText('Durée standard d’un rendez-vous en minutes (ex : 30, 45, 60…).'),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacité simultanée')
                            ->numeric()
                            ->required()
                            ->helperText('Nombre maximum de rendez-vous pouvant être pris en même temps sur ce créneau.'),

                        // Forms\Components\TextInput::make('service_id')
                        //     ->label('Service ID')
                        //     ->numeric()
                        //     ->nullable()
                        //     ->helperText('Identifiant du service lié (à relier plus tard à la table des services).'),
                    ]),
            ]),

        Forms\Components\Section::make('Statut')
            ->schema([
                Forms\Components\Toggle::make('status')
                    ->label('Actif ?')
                    ->default(true)
                    ->helperText('Activez pour rendre ce créneau disponible à la réservation. Désactivez pour le désactiver temporairement.'),
            ])
            ->columns(1),
    ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                  Tables\Columns\TextColumn::make('staff.fullName')
                    ->label('Staff'),
                Tables\Columns\IconColumn::make('status')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('weekday_label')
                    ->label('Jour')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Début')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('slot_duration')
                    ->label('Durée (min)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacité')
                    ->sortable(),

              
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('weekday');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAvailabilities::route('/'),
            // 'create' => Pages\CreateAvailability::route('/create'),
            // 'edit'   => Pages\EditAvailability::route('/{record}/edit'),
        ];
    }
}
