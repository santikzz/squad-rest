<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?int $navigationSort = 2;

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
                TextColumn::make('name')
                    ->state(function (User $record): string {
                        return $record->name . ' ' . $record->surname;
                    })
                    ->searchable()
                    ->copyable(),
                TextColumn::make('email')
                    ->icon('heroicon-s-envelope')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied to your clipboard!'),
                TextColumn::make('Groups owned')
                    ->state(function (User $record): string {
                        return $record->ownedGroups->count();
                    })
                    ->alignCenter(),
                TextColumn::make('Groups joined')
                    ->state(function (User $record): string {
                        return $record->joinedGroups->count();
                    })
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->icon('heroicon-s-calendar'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->recordUrl('');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
