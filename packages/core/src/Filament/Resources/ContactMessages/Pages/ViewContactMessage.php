<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ContactMessages\Pages;

use Reyhan\Core\Filament\Resources\ContactMessages\ContactMessageResource;
use Reyhan\Core\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

/**
 * @property ContactMessage $record
 */
class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleRead')
                ->label(fn () => $this->record->is_read ? 'علامت‌گذاری به عنوان خوانده‌نشده' : 'علامت‌گذاری به عنوان خوانده‌شده')
                ->icon(fn () => $this->record->is_read ? 'heroicon-o-envelope' : 'heroicon-o-envelope-open')
                ->color(fn () => $this->record->is_read ? 'warning' : 'success')
                ->action(function () {
                    $this->record->update(['is_read' => ! $this->record->is_read]);
                    $this->refreshFormData(['is_read']);
                }),
            DeleteAction::make(),
        ];
    }
}
