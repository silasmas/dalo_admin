<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Qdb\QdbAnswer;
use App\Enums\Qdb\AnswerStatus;
use App\Models\Qdb\QdbQuestion;
use Filament\Resources\Resource;
use App\Filament\Resources\QdbAnswerResource\Pages;
use App\Filament\Resources\QuestionResource\Widgets\QuestionStats;

class QdbAnswerResource extends Resource
{
    protected static ?string $model = QdbAnswer::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Réponses';
    protected static ?string $modelLabel = 'Réponse';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // 1. Informations générales
        Forms\Components\Section::make('Informations générales')
            ->columns(2)
            ->schema([
            Forms\Components\Select::make('question_id')
                ->label('Question')
                ->searchable()->preload()->required()
                ->options(QdbQuestion::query()->orderByDesc('id')->limit(500)->pluck('title','id')->toArray())
                ->helperText('À quelle question répond-on ?'),

            Forms\Components\Select::make('answer_status')
                ->label('Statut')->native(false)->required()
                ->options(collect(AnswerStatus::cases())->mapWithKeys(fn($c)=>[$c->value=>$c->label()])->all()),

            Forms\Components\Toggle::make('is_official')->label('Réponse officielle ?')->default(false),
            Forms\Components\Toggle::make('is_accepted')->label('Réponse acceptée ?')->default(false)
                ->helperText('Si coché, fera le lien “réponse acceptée” sur la question.'),

            Forms\Components\MarkdownEditor::make('answer')
                ->label('Réponse')->required()->columnSpanFull(),

            Forms\Components\KeyValue::make('versets_refs_json')
                ->label('Versets (clé/valeur)')->keyLabel('Réf.')->valueLabel('Texte ou lien'),
            Forms\Components\KeyValue::make('sources_json')
                ->label('Sources (clé/valeur)')->keyLabel('Nom')->valueLabel('Lien'),
            ]),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('question.title')->label('Question')->limit(40)->searchable(),
            Tables\Columns\BadgeColumn::make('answer_status')->label('Statut')
                // ->formatStateUsing(fn($s)=>AnswerStatus::tryFrom((string)$s)?->label() ?? $s)
                ->colors([
                    'success' => fn($record)=>$record === AnswerStatus::Published->value,
                    'warning' => fn($record)=>$record === AnswerStatus::Draft->value,
                    'gray'    => fn($record)=>$record === AnswerStatus::Hidden->value,
                ]),
            Tables\Columns\IconColumn::make('is_official')->label('Officiel')->boolean(),
            Tables\Columns\IconColumn::make('is_accepted')->label('Acceptée')->boolean(),
            Tables\Columns\TextColumn::make('nb_likes')->label('Likes')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Créée le')->dateTime('d/m/Y H:i')->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('setAccepted')
                ->label('Marquer comme réponse acceptée')->color('primary')->icon('heroicon-o-check-circle')
                ->visible(fn(QdbAnswer $a)=> !$a->is_accepted)
                ->requiresConfirmation()
                ->action(function (QdbAnswer $a) {
                    $a->update(['is_accepted'=>true]);
                    $a->question()->update(['answer_id'=>$a->id]);
                }),
            Tables\Actions\Action::make('unsetAccepted')
                ->label('Retirer “acceptée”')->color('warning')->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn(QdbAnswer $a)=> $a->is_accepted)
                ->action(function (QdbAnswer $a) {
                    $a->update(['is_accepted'=>false]);
                    if (optional($a->question)->answer_id === $a->id) {
                        $a->question()->update(['answer_id'=>null]);
                    }
                }),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
        ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth('admin')->id();
        $data['nb_likes'] = $data['nb_likes'] ?? 0;
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
            'index'  => Pages\ListQdbAnswers::route('/'),
            'create' => Pages\CreateQdbAnswer::route('/create'),
            'edit'   => Pages\EditQdbAnswer::route('/{record}/edit'),
        ];
    }
     public static function getHeaderWidgets(): array
    {
        return [
            QuestionStats::class,
        ];
    }
}
