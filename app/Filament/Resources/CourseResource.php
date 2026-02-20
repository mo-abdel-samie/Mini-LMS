<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Modules\Course\Models\Course;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'LMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Details')
                    ->schema([
                        Forms\Components\Select::make('level_id')
                            ->relationship('level', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(?string $state, Set $set) => $set('slug', Str::slug($state)))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Course Image')
                            ->image()
                            ->directory('courses')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_published')
                            ->required(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->hiddenOn('create')
                            ->readOnly(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Lessons')
                    ->description('Create and reorder lessons for this course.')
                    ->schema([
                        Forms\Components\Repeater::make('lessons')
                            ->label('Lessons')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (! isset($state['title'])) {
                                    return null;
                                }

                                $order = $state['order'] ?? null;

                                return $order !== null
                                    ? "{$order}. {$state['title']}"
                                    : $state['title'];
                            })
                            ->schema([
                                Forms\Components\Hidden::make('id'),
                                Forms\Components\TextInput::make('title')
                                    ->label('Lesson Title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('video_url')
                                    ->label('Video URL')
                                    ->url()
                                    ->required(),
                                Forms\Components\Toggle::make('is_free_preview')
                                    ->label('Free Preview')
                                    ->default(false),
                            ])
                            ->addActionLabel('Add Lesson')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level.name')
                    ->label('Level')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Enrollments')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completions_count')
                    ->label('Completions')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withCount([
                'enrollments',
                'completions',
            ]);
    }
}
