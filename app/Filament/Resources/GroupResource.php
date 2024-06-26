<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;




class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationLabel = 'Groups';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    // protected static ?string $navigationGroup = 'Groups';
    protected static ?int $navigationSort = 1;

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

                Stack::make([

                    Split::make([
                        TextColumn::make('privacy')
                            ->state(function (Group $record): string {
                                return strtoupper($record->privacy);
                            })
                            ->badge()
                            ->colors([
                                'success' => 'OPEN',
                                'warning' => 'CLOSED',
                                'danger' => 'PRIVATE',
                            ])
                            ->sortable()
                            ->grow(false),
                        TextColumn::make('title')
                            ->limit(64)
                            ->grow(false)
                            ->searchable(),
                        // TextColumn::make('tags.tag')
                        //     ->badge()
                        //     ->color('info'),


                    ]),

                    Panel::make([

                        Stack::make([

                            Split::make([
                                TextColumn::make('owner.name')
                                    ->state(function (Group $record): string {
                                        return $record->owner->name . ' ' . $record->owner->surname;
                                    })
                                    ->icon('heroicon-s-user')
                                    ->sortable()
                                    ->searchable()
                                    ->grow(false),
                                TextColumn::make('created_at')
                                    ->dateTime('M j, Y')
                                    ->sortable()
                                    ->searchable()
                                    ->icon('heroicon-s-calendar')
                                    ->grow(false),
                                TextColumn::make('memebers')
                                    ->state(function (Group $record): string {
                                        $limit = $record->max_members != null ? ' / ' . $record->max_members : '';
                                        return $record->members->count() + 1 . $limit;
                                    })
                                    ->icon('heroicon-s-user')
                                    ->grow(false)
                                    ->alignRight(),
                                TextColumn::make('carrera')
                                    ->state(function (Group $record): string {
                                        return $record->carrera->facultad->name . ' - ' . $record->carrera->name;
                                    })
                                    ->icon('heroicon-s-academic-cap'),
                            ]),

                            TextColumn::make('description')
                                ->searchable()
                                ->color('gray'),
                            TextColumn::make('members.name')
                                ->state(function (Group $record): string {
                                    $members = [];
                                    foreach ($record->members as $member) {
                                        array_push($members, $member->name . ' ' . $member->surname);
                                    }
                                    return implode(', ', $members);
                                })
                                ->searchable()
                                ->color('gray'),

                        ])
                            ->collapsible()
                            ->space(3),

                    ]), // end panel

                ])
                    ->space(3), // end stack


            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->recordUrl('');;
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
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
