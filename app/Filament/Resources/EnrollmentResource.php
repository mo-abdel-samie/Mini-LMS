<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Course\Models\Lesson;
use Modules\Enrollment\Models\Enrollment;
use Modules\Progres\Models\CourseCompletion;
use Modules\Progres\Models\LessonProgress;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'LMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('enrolled_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->badge()
                    ->getStateUsing(function (Enrollment $record): string {
                        $progress = self::resolveProgress($record);

                        return "{$progress['completed']}/{$progress['total']} ({$progress['percentage']}%)";
                    })
                    ->color(function (Enrollment $record): string {
                        $progress = self::resolveProgress($record);

                        return match (true) {
                            $progress['percentage'] >= 100 => 'success',
                            $progress['percentage'] >= 50 => 'warning',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completed')
                    ->boolean()
                    ->getStateUsing(fn (Enrollment $record): bool => self::resolveProgress($record)['is_completed']),
                Tables\Columns\TextColumn::make('enrolled_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'course']);
    }

    /**
     * @return array{completed: int, total: int, percentage: int, is_completed: bool}
     */
    protected static function resolveProgress(Enrollment $record): array
    {
        static $cache = [];

        $key = "{$record->user_id}:{$record->course_id}";

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $totalLessons = Lesson::query()
            ->where('course_id', $record->course_id)
            ->count();

        $completedLessons = LessonProgress::query()
            ->where('user_id', $record->user_id)
            ->where('course_id', $record->course_id)
            ->count();

        $isCompleted = CourseCompletion::query()
            ->where('user_id', $record->user_id)
            ->where('course_id', $record->course_id)
            ->exists();

        $percentage = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : 0;

        $cache[$key] = [
            'completed' => $completedLessons,
            'total' => $totalLessons,
            'percentage' => $percentage,
            'is_completed' => $isCompleted,
        ];

        return $cache[$key];
    }
}
