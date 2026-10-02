<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Pages;

use Reyhan\Core\Actions\System\PerformCoreUpdateAction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class SystemUpdatePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    public static function getNavigationLabel(): string
    {
        return __('Core Update');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System Settings');
    }

    protected static ?int $navigationSort = 4;

    public function getTitle(): string|Htmlable
    {
        return __('System & Core Engine Update Center');
    }

    protected string $view = 'filament.pages.system-update-page';

    /**
     * @var list<string>
     */
    public array $updateLogs = [];

    public ?string $lastUpdateMessage = null;

    public bool $isSuccess = false;

    public bool $isUpdating = false;

    public function runUpdate(?PerformCoreUpdateAction $action = null): void
    {
        $action ??= app(PerformCoreUpdateAction::class);

        $this->isUpdating = true;
        $result = $action->execute(skipBackup: false);

        $this->updateLogs = $result->logs;
        $this->lastUpdateMessage = $result->message;
        $this->isSuccess = $result->success;
        $this->isUpdating = false;

        if ($result->success) {
            Notification::make()
                ->title(__('Core Engine Updated Successfully'))
                ->body($result->message)
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('Core Update Failed'))
                ->body($result->message)
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('triggerUpdate')
                ->label(__('Execute Safe Update'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('System & Core Engine Update Center'))
                ->modalDescription(__('Execute automated zero-downtime database migrations, refresh Filament assets, compile runtime caches, and reload FrankenPHP Octane workers with automatic database snapshot guarantees.'))
                ->modalSubmitActionLabel(__('Execute Safe Update'))
                ->action(fn () => $this->runUpdate()),
        ];
    }
}
