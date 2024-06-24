<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportsResource\Pages;
use App\Filament\Resources\ReportsResource\RelationManagers;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportsResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Others';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reporter')->state(function (Report $record): string {
                    return $record->reporter->name.' '.$record->reporter->name;
                })
                ->label('Reporter Name')
                ->copyable(),
                TextColumn::make('reported')->state(function (Report $record): string {
                    return $record->reported->name.' '.$record->reported->name.' ('.$record->reported->ulid.')';
                })
                ->label('Reported Name')
                ->copyable(),
                TextColumn::make('description')
                ->copyable(),
                TextColumn::make('created_at')
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListReports::route('/'),
            // 'create' => Pages\CreateReports::route('/create'),
            // 'edit' => Pages\EditReports::route('/{record}/edit'),
        ];
    }

    // add this to remove CREATE button on top of the table :)
    public static function canCreate(): bool
    {
        return false;
    }
}
