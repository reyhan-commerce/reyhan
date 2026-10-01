<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Http\Controllers\Api\V1\OrderInvoiceController;
use App\Models\Order;
use BokshornIt\FilamentActivityTimeline\Actions\ActivityTimelineAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_invoice')
                ->label('چاپ فاکتور')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn (Order $record): string => OrderInvoiceController::generateAdminInvoiceUrl($record))
                ->openUrlInNewTab(),
            ActivityTimelineAction::make()
                ->withRelations(['items', 'payments'])
                ->icon('heroicon-o-clock')
                ->limit(30),
            EditAction::make(),
        ];
    }
}
