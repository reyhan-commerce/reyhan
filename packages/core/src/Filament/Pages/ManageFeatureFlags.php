<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Pages;

use Reyhan\Core\Features\ShopFeature;
use Reyhan\Core\Http\Controllers\Api\V1\AppFeaturesController;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Laravel\Pennant\Feature;
use UnitEnum;

class ManageFeatureFlags extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'مدیریت قابلیت‌ها (فیچرها)';

    protected static ?string $title = 'مدیریت قابلیت‌ها و فیچرفلگ‌ها';

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات سیستم';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-feature-flags';

    /**
     * @var array<string, bool>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $features = [];
        foreach (ShopFeature::names() as $name) {
            $features[$name] = (bool) Feature::active($name);
        }

        $this->data = $features;
    }

    public function form(Schema $schema): Schema
    {
        $toggles = [];
        foreach (ShopFeature::all() as $name => $label) {
            $toggles[] = Toggle::make($name)
                ->label($label)
                ->helperText("کلید سیستمی: {$name}")
                ->live()
                ->afterStateUpdated(function (bool $state) use ($name) {
                    if ($state) {
                        Feature::activate($name);
                    } else {
                        Feature::deactivate($name);
                    }

                    Cache::forget(AppFeaturesController::CACHE_KEY);

                    Notification::make()
                        ->title("وضعیت فیچر {$name} به‌روزرسانی شد")
                        ->success()
                        ->send();
                });
        }

        return $schema
            ->statePath('data')
            ->components([
                Section::make('کنترل فعال‌سازی قابلیت‌های فروشگاه')
                    ->description('با تغییر هر کلید، وضعیت فیچر در لحظه در سیستم و فرانت‌اند تغییر می‌کند.')
                    ->schema($toggles)
                    ->columns(2),
            ]);
    }
}
