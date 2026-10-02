<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Reviews;

use Reyhan\Core\Enums\ReviewStatus;
use Reyhan\Core\Filament\Resources\Reviews\Pages\CreateReview;
use Reyhan\Core\Filament\Resources\Reviews\Pages\EditReview;
use Reyhan\Core\Filament\Resources\Reviews\Pages\ListReviews;
use Reyhan\Core\Filament\Resources\Reviews\Pages\ViewReview;
use Reyhan\Core\Models\Review;
use Reyhan\Core\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $navigationLabel = 'نظرات و امتیازات';

    protected static ?string $modelLabel = 'دیدگاه';

    protected static ?string $pluralModelLabel = 'نظرات مشتریان';

    protected static string|UnitEnum|null $navigationGroup = 'مشتریان و بازخورد';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('وضعیت و مدیریت دیدگاه')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('وضعیت انتشار')
                            ->options(array_combine(
                                array_map(fn (ReviewStatus $s): string => $s->value, ReviewStatus::cases()),
                                array_map(fn (ReviewStatus $s): string => $s->label(), ReviewStatus::cases())
                            ))
                            ->required(),

                        TextInput::make('rating')
                            ->label('امتیاز کلی (۱ تا ۵)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),

                        Textarea::make('comment')
                            ->label('متن نظر مشتری')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('admin_reply')
                            ->label('پاسخ فروشگاه به دیدگاه')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('جزئیات دیدگاه و شاخص‌های کیفی')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('محصول')
                            ->weight('bold'),

                        TextEntry::make('user.mobile')
                            ->label('ثبت‌کننده')
                            ->default(function (Review $record): string {
                                $user = $record->user;

                                return $user instanceof User ? ($user->full_name.' ('.$user->mobile.')') : 'مهمان';
                            }),

                        TextEntry::make('status')
                            ->label('وضعیت')
                            ->badge()
                            ->formatStateUsing(fn (ReviewStatus $state): string => $state->label())
                            ->color(fn (ReviewStatus $state): string => $state->color()),

                        TextEntry::make('rating')
                            ->label('امتیاز کلی')
                            ->suffix(' / ۵'),

                        TextEntry::make('criteria_ratings')
                            ->label('شاخص‌های کیفی')
                            ->formatStateUsing(function ($state, Review $record): string {
                                $criteria = $state ?: [
                                    'longevity' => $record->longevity_rating ?? 5,
                                    'coverage' => $record->coverage_rating ?? 5,
                                    'value' => $record->value_rating ?? 5,
                                ];
                                if (! is_array($criteria)) {
                                    return '-';
                                }
                                $labels = [
                                    'longevity' => 'ماندگاری / دوام',
                                    'coverage' => 'کیفیت / پوشش',
                                    'value' => 'ارزش خرید',
                                    'quality' => 'کیفیت ساخت',
                                    'durability' => 'استحکام',
                                    'fit' => 'تطابق سایز',
                                ];
                                $items = [];
                                foreach ($criteria as $k => $v) {
                                    $name = $labels[$k] ?? (string) $k;
                                    $items[] = "{$name}: {$v}/۵";
                                }

                                return implode(' | ', $items);
                            })
                            ->columnSpan(2),

                        IconEntry::make('is_verified_purchase')
                            ->label('خریدار قطعی این کالا')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->label('زمان ثبت')
                            ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-'),

                        TextEntry::make('comment')
                            ->label('متن دیدگاه مشتری')
                            ->columnSpanFull(),

                        TextEntry::make('admin_reply')
                            ->label('پاسخ رسمی پشتیبانی')
                            ->default('هنوز پاسخی ثبت نشده است')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('محصول')
                    ->searchable()
                    ->limit(35)
                    ->weight('bold'),

                TextColumn::make('user.mobile')
                    ->label('کاربر')
                    ->searchable()
                    ->default(function (Review $record): string {
                        $user = $record->user;

                        return $user instanceof User ? ($user->first_name ?? $user->mobile) : 'مهمان';
                    }),

                TextColumn::make('rating')
                    ->label('امتیاز')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning')
                    ->sortable(),

                IconColumn::make('is_verified_purchase')
                    ->label('خریدار')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckBadge)
                    ->falseIcon(Heroicon::OutlinedMinus),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (ReviewStatus $state): string => $state->label())
                    ->color(fn (ReviewStatus $state): string => $state->color())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d') : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('هنوز دیدگاهی ثبت نشده است')
            ->emptyStateDescription('هنگامی که مشتریان برای کالاهای خریداری شده نظر یا امتیاز ثبت کنند، دیدگاه‌ها در اینجا بررسی و تایید می‌شوند.')
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('status')
                    ->label('فیلتر بر اساس وضعیت')
                    ->options(array_combine(
                        array_map(fn (ReviewStatus $s): string => $s->value, ReviewStatus::cases()),
                        array_map(fn (ReviewStatus $s): string => $s->label(), ReviewStatus::cases())
                    )),

                TernaryFilter::make('is_verified_purchase')
                    ->label('خریدار واقعی')
                    ->placeholder('همه')
                    ->trueLabel('فقط خریداران واقعی')
                    ->falseLabel('همه کاربران'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label('تایید')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Review $record): bool => $record->status !== ReviewStatus::Approved)
                    ->action(function (Review $record): void {
                        $record->update(['status' => ReviewStatus::Approved]);
                        Notification::make()->title('دیدگاه با موفقیت تایید شد')->success()->send();
                    }),
                Action::make('reject')
                    ->label('رد')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->status !== ReviewStatus::Rejected)
                    ->action(function (Review $record): void {
                        $record->update(['status' => ReviewStatus::Rejected]);
                        Notification::make()->title('دیدگاه رد شد')->warning()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'view' => ViewReview::route('/{record}'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
