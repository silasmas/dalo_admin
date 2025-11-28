<?php
namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Service;
use Filament\Forms\Form;
use App\Enums\UserStatus;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ShopOrderState;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Notifications\Notification;
use App\Filament\Resources\ServiceResource\Pages;

class ServiceResource extends Resource
{
    protected static ?string $model           = Service::class;
    protected static ?string $navigationIcon  = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Services';
    protected static ?string $modelLabel      = 'Service';

    public static function form(Form $form): Form
    {
        return $form->schema([
             Forms\Components\Section::make('Informations')
                    ->columnSpan(12)
                    ->schema([
            Forms\Components\TextInput::make('name')->label('Nom')->required()
                ->helperText('Nom du service (ex.: “Intercession”, “Accueil”).')
                ->live(onBlur: true)
                ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(191)
                ->helperText('Chemin URL unique.'),
            Forms\Components\Textarea::make('description')->label('Description')->rows(3),
            Forms\Components\TextInput::make('status')->label('Statut')->numeric()->default(1)
                ->helperText('1=actif, 0=inactif.'),
            Forms\Components\Select::make('magener_id')->label('Responsable')
                ->searchable()->preload()->options(
                User::query()->orderBy('lastname')->get()->pluck('full_name', 'id')
            )
                ->helperText('Serviteur (user) qui gère la prise de rendez-vous.'),
                 ]),
        ]);
    }

    public static function table(Table $table): Table
    {

        return $table->defaultSort('id', 'desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
            Tables\Columns\TextColumn::make('slug')->label('Slug')->toggleable(),
            // 🔽 Statut 0/1 éditable dans la liste
            SelectColumn::make('status')
                ->label('Actif ?')
                ->options([
                    1 => 'Actif',
                    0 => 'Inactif',
                ])
                ->selectablePlaceholder(false)
                ->sortable(),
            Tables\Columns\TextColumn::make('manager.full_name')->label('Responsable'),
            Tables\Columns\BadgeColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn($state) => UserStatus::tryFrom((int) $state)?->label() ?? 'Inconnu')
                ->colors([
                    'success' => fn($state) => (int) $state === UserStatus::Activated->value,
                    'warning' => fn($state) => (int) $state === UserStatus::Pending->value,
                    'danger'  => fn($state)  => (int) $state === UserStatus::Disabled->value,
                    'gray'    => fn($state)    => (int) $state === UserStatus::Deleted->value,
                    'info'    => fn($state)    => (int) $state === UserStatus::ValidatedAwaitingInfo->value,
                    'primary' => fn($state) => (int) $state === UserStatus::CreatingAwaitingOtp->value,
                ])
                ->sortable(),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
        ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {$data['created_by'] = auth('admin')->id();return $data;}
    public static function mutateFormDataBeforeSave(array $data): array
    {$data['updated_by'] = auth('admin')->id();return $data;}

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view'   => Pages\ViewService::route('/{record}'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }


}
