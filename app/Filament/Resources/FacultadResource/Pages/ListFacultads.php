<?php

namespace App\Filament\Resources\FacultadResource\Pages;

use App\Filament\Resources\FacultadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListFacultads extends ListRecords
{
    protected static string $resource = FacultadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // public function getTabs() : array {
    //     return [
    //         'All' => Tab::make(),
    //     ];
    // }

}
