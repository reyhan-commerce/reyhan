<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Orders\Pages;

use Reyhan\Core\Filament\Resources\Orders\OrderResource;
use BokshornIt\FilamentActivityTimeline\Actions\ActivityTimelineAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActivityTimelineAction::make()
                ->withRelations(['items', 'payments'])
                ->icon('heroicon-o-clock')
                ->limit(30),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
