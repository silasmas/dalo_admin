<?php
namespace App\Filament\Resources;

use App\Enums\MessageChannel;
use App\Filament\Resources\MsgMessageResource\Pages;
use App\Models\MsgMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MsgMessageResource extends Resource
{
    protected static ?string $model           = MsgMessage::class;
    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Messages';
    protected static ?string $modelLabel      = 'Message';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Métadonnées')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('channel')
                        ->label('Canal')
                        ->options(collect(MessageChannel::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all())
                        ->native(false)->required()
                        ->helperText('Choisissez le canal d’origine du message.'),

                    Forms\Components\Select::make('priority')
                        ->label('Priorité')
                        ->native(false)
                        ->options([1 => 'Basse', 2 => 'Normale', 3 => 'Haute', 4 => 'Urgente'])
                        ->helperText('Indique l’urgence du traitement.'),

                    Forms\Components\DateTimePicker::make('closed_at')
                        ->label('Clôturé le')
                        ->helperText('Rempli lors de la clôture du ticket.')
                        ->native(false),
                ]),

            Forms\Components\Section::make('Émetteur')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('from_name')->label('Nom')->maxLength(512)
                        ->helperText('Nom de la personne qui a envoyé le message.'),
                    Forms\Components\TextInput::make('from_email')->label('Email')->email()->maxLength(512)
                        ->helperText('Adresse email de l’émetteur.'),
                    Forms\Components\TextInput::make('from_phone')->label('Téléphone')->maxLength(50)
                        ->helperText('Téléphone de contact (optionnel).'),
                ]),

            Forms\Components\Section::make('Contenu')
                ->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->label('Sujet')->required()->maxLength(255)
                        ->helperText('Objet du message.'),
                    Forms\Components\MarkdownEditor::make('body')
                        ->label('Message')->required()->columnSpanFull()
                        ->helperText('Contenu détaillé du message.'),
                    // Upload multiples -> S3 (public/privé ok)
                    Forms\Components\FileUpload::make('attachments_json')
                        ->label('Pièces jointes')
                        ->helperText('Ajoutez des fichiers (images, PDF, etc.). Stockés sur S3.')
                        ->multiple()
                        ->disk('s3')
                        ->directory('messages/' . now()->format('Y/m'))
                        ->visibility('private') // active si ton bucket est public
                        ->preserveFilenames(false)
                        ->getUploadedFileNameForStorageUsing(
                            function (TemporaryUploadedFile $file, ?Model $record): string {
                                $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                                return $base . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                            }
                        )
                        ->previewable()
                        ->downloadable(),
                ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\BadgeColumn::make('channel')
                    ->label('Canal')
                    ->formatStateUsing(fn($state) => MessageChannel::tryFrom((string) $state)?->label() ?? $state)
                    ->colors([
                        'primary' => fn($state) => $state === MessageChannel::Contact->value,
                        'info'    => fn($state)    => $state === MessageChannel::Support->value,
                        'warning' => fn($state) => $state === MessageChannel::Feedback->value,
                        'success' => fn($state) => $state === MessageChannel::Prayer->value,
                        'gray'    => fn($state)    => $state === MessageChannel::Other->value,
                    ]),
                Tables\Columns\TextColumn::make('subject')->label('Sujet')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('from_name')->label('De')->toggleable()->limit(24),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prio.')
                    ->badge()
                    ->colors(['success' => [1], 'primary' => [2], 'warning' => [3], 'danger' => [4]]),
                Tables\Columns\TextColumn::make('created_at')->label('Reçu le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->options(collect(MessageChannel::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all()),
                Tables\Filters\TernaryFilter::make('closed')->label('Clôturé')
                    ->queries(
                        true: fn($q)  => $q->whereNotNull('closed_at'),
                        false: fn($q) => $q->whereNull('closed_at'),
                        blank: fn($q) => $q
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(): bool => Auth::user()?->can('update_msg::message')),
                Tables\Actions\Action::make('close')
                    ->label('Clôturer')
                    ->color('success')->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(fn(MsgMessage $record) => $record->update(['closed_at' => now()])),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ]);
    }

    // Audit automatiques
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth('admin')->id();
        return $data;
    }
    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth('admin')->id();
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMsgMessages::route('/'),
            'create' => Pages\CreateMsgMessage::route('/create'),
            'view'   => Pages\ViewMsgMessage::route('/{record}'),
            'edit'   => Pages\EditMsgMessage::route('/{record}/edit'),
        ];
    }
}
