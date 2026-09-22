<?php

declare(strict_types=1);

namespace App\Filament\Resources\LoyaltyTransactions\Pages;

use App\Filament\Resources\LoyaltyTransactions\LoyaltyTransactionResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyTransactions extends ListRecords
{
    protected static string $resource = LoyaltyTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manualAward')
                ->label('ثبت یا کسر دستی امتیاز')
                ->icon('heroicon-o-gift')
                ->color('primary')
                ->modalHeading('ثبت تراکنش امتیاز برای مشتری')
                ->form([
                    Select::make('user_id')
                        ->label('انتخاب مشتری')
                        ->options(fn () => User::query()->pluck('mobile', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('points')
                        ->label('تعداد امتیاز (برای کسر، علامت منفی بگذارید)')
                        ->numeric()
                        ->placeholder('مثال: 50 یا -20')
                        ->required(),

                    TextInput::make('description')
                        ->label('علت / شرح ثبت امتیاز')
                        ->placeholder('مثال: پاداش وفاداری / مسابقه اینستاگرام')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $user = User::find($data['user_id']);
                    if (! $user) {
                        return;
                    }

                    $points = (int) $data['points'];
                    $user->awardLoyaltyPoints(
                        points: $points,
                        type: 'manual_adjustment',
                        description: $data['description']
                    );

                    Notification::make()
                        ->title('تراکنش امتیاز با موفقیت ثبت شد')
                        ->success()
                        ->send();
                }),
        ];
    }
}
