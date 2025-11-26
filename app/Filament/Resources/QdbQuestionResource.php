<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Pages\EditQdbQuestion;
use Pages\ViewQdbQuestion;
use Illuminate\Support\Str;
use Pages\CreateQdbQuestion;
use App\Models\Qdb\QdbQuestion;
use App\Enums\Qdb\QuestionState;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\QdbQuestionResource\Pages;
use App\Filament\Resources\QuestionResource\Widgets\QuestionStats;

class QdbQuestionResource extends Resource
{
    protected static ?string $model = QdbQuestion::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Questions';
    protected static ?string $modelLabel = 'Question';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Question')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre')->required()->maxLength(255)
                        ->helperText('Titre clair et concis.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')->maxLength(191)
                        ->unique(ignoreRecord: true, table: 'qdb_questions', column: 'slug')
                        ->helperText('Chemin URL unique.'),
                    Forms\Components\Select::make('state')
                        ->label('État')->native(false)->required()
                        ->options(collect(QuestionState::cases())->mapWithKeys(fn($c)=>[$c->value=>$c->label()])->all())
                        ->helperText('Visibilité / cycle de vie.'),
                    Forms\Components\Textarea::make('categories_tags')
                        ->label('Catégories / Tags (CSV)')
                        ->rows(2)
                        ->helperText('Ex: foi,baptême,grâce'),
                ]),
            Forms\Components\Section::make('Contenu')
                ->schema([
                    Forms\Components\MarkdownEditor::make('body')
                        ->label('Corps de la question')->required()
                        ->helperText('Développe clairement la question.')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Auteur')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('author_name')->label('Nom')->maxLength(191),
                    Forms\Components\TextInput::make('author_email')->label('Email')->email()->maxLength(191),
                    Forms\Components\TextInput::make('author_phone')->label('Téléphone')->maxLength(191),
                ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(48),
            // Tables\Columns\BadgeColumn::make('state')->label('État')
            //     ->formatStateUsing(fn($record) => QuestionState::tryFrom((string)$record)?->label() ?? $record)
            //     ->colors([
            //         'gray'    => fn($record) => $record === QuestionState::Draft->value,
            //         'success' => fn($record) => $record === QuestionState::Published->value,
            //         'warning' => fn($record) => $record === QuestionState::Hidden->value,
            //         'danger'  => fn($record) => $record === QuestionState::Archived->value,
            //     ]),
            Tables\Columns\BadgeColumn::make('state')->label('État')
                // ->formatStateUsing(fn($record) => QuestionState::tryFrom((string)$record)?->label() ?? $record)
                ->colors([
                    'gray'    => fn($record) => $record === QuestionState::Draft->value,
                    'success' => fn($record) => $record === QuestionState::Published->value,
                    'warning' => fn($record) => $record === QuestionState::Hidden->value,
                    'danger'  => fn($record) => $record === QuestionState::Archived->value,
                ]),
            Tables\Columns\TextColumn::make('nb_likes')->label('Likes')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Créée le')->dateTime('d/m/Y H:i')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('state')
                ->options(collect(QuestionState::cases())->mapWithKeys(fn($c)=>[$c->value=>$c->label()])->all()),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('publish')
                ->label('Publier')->icon('heroicon-o-megaphone')->color('success')
                ->visible(fn(QdbQuestion $r) => $r->state !== QuestionState::Published)
                ->requiresConfirmation()
                ->action(fn(QdbQuestion $r) => $r->update(['state'=>QuestionState::Published])),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
        ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth('admin')->id();
        $data['nb_likes']   = $data['nb_likes'] ?? 0;
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
            'index'  =>Pages\ListQdbQuestions::route('/'),
            'create' => Pages\CreateQdbQuestion::route('/create'),
            // 'view'   => Pages\ViewQdbQuestion::route('/{record}'),
            'edit'   => Pages\EditQdbQuestion::route('/{record}/edit'),
        ];
    }
     public static function getNavigationBadge(): ?string
    {
        $count = QdbQuestion::query()->count();   // ou un filtre : where('status',1)
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    public static function getHeaderWidgets(): array
    {
        return [
            QuestionStats::class,
        ];
    }
}
