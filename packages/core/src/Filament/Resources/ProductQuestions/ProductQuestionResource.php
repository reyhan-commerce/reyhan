<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ProductQuestions;

use Reyhan\Core\Filament\Resources\ProductQuestions\Pages\ListProductQuestions;
use Reyhan\Core\Models\ProductQuestion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class ProductQuestionResource extends Resource
{
    protected static ?string $model = ProductQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'پرسش و پاسخ کالاها';

    protected static ?string $modelLabel = 'پرسش کاربر';

    protected static ?string $pluralModelLabel = 'پرسش و پاسخ‌ها';

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه و محصولات';

    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        $count = ProductQuestion::query()->where('is_approved', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('نام کالا')
                    ->searchable()
                    ->weight('bold')
                    ->limit(35),

                TextColumn::make('user.name')
                    ->label('پرسش‌کننده')
                    ->state(fn (ProductQuestion $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : 'مهمان'),

                TextColumn::make('question')
                    ->label('متن پرسش')
                    ->searchable()
                    ->limit(50),

                IconColumn::make('is_approved')
                    ->label('تأیید شده')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('answers_count')
                    ->label('پاسخ‌ها')
                    ->counts('answers')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('زمان ثبت')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('وضعیت انتشار')
                    ->placeholder('همه پرسش‌ها')
                    ->trueLabel('فقط تأیید شده‌ها')
                    ->falseLabel('در انتظار تأیید'),
            ])
            ->recordActions([
                Action::make('toggle_approval')
                    ->label(fn (ProductQuestion $record): string => $record->is_approved ? 'عدم تأیید' : 'تأیید پرسش')
                    ->icon(fn (ProductQuestion $record): Heroicon => $record->is_approved ? Heroicon::OutlinedXMark : Heroicon::OutlinedCheck)
                    ->color(fn (ProductQuestion $record): string => $record->is_approved ? 'danger' : 'success')
                    ->action(function (ProductQuestion $record): void {
                        $record->update(['is_approved' => ! $record->is_approved]);
                        Notification::make()->title('وضعیت انتشار تغییر کرد')->success()->send();
                    }),

                Action::make('answer')
                    ->label('ارسال پاسخ رسمی')
                    ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
                    ->color('primary')
                    ->form([
                        Textarea::make('answer')
                            ->label('متن پاسخ رسمی کارشناس')
                            ->required()
                            ->rows(4)
                            ->placeholder('پاسخ تخصصی به پرسش مشتری پیرامون این کالا...'),
                    ])
                    ->action(function (ProductQuestion $record, array $data): void {
                        // Ensure question is approved as well
                        if (! $record->is_approved) {
                            $record->update(['is_approved' => true]);
                        }

                        $record->answers()->create([
                            'user_id' => auth()->id(),
                            'answer' => $data['answer'],
                            'is_approved' => true,
                            'is_staff' => true,
                        ]);

                        Notification::make()
                            ->title(__('messages.questions.staff_answer_published'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductQuestions::route('/'),
        ];
    }
}
