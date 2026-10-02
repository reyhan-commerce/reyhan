<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ContactMessages;

use Reyhan\Core\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use Reyhan\Core\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use Reyhan\Core\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $navigationLabel = 'پیام‌های تماس';

    protected static ?string $modelLabel = 'پیام کاربر';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس با ما';

    protected static string|UnitEnum|null $navigationGroup = 'مشتریان و بازخورد';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $unreadCount = ContactMessage::query()->unread()->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات و متن پیام مشتری')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام و نام خانوادگی')
                            ->disabled(),

                        TextInput::make('mobile')
                            ->label('شماره همراه')
                            ->tel()
                            ->disabled(),

                        TextInput::make('subject')
                            ->label('موضوع پیام')
                            ->columnSpanFull()
                            ->disabled(),

                        Textarea::make('message')
                            ->label('متن پیام')
                            ->rows(6)
                            ->columnSpanFull()
                            ->disabled(),

                        Toggle::make('is_read')
                            ->label('وضعیت خوانده‌شده')
                            ->helperText('آیا به این پیام رسیدگی شده است؟'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('فرستنده')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('mobile')
                    ->label('شماره تماس')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('subject')
                    ->label('موضوع')
                    ->searchable()
                    ->placeholder('بدون موضوع')
                    ->limit(35),

                TextColumn::make('message')
                    ->label('متن پیام')
                    ->limit(50)
                    ->wrap(),

                IconColumn::make('is_read')
                    ->label('خوانده شده')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ارسال')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('وضعیت خوانده‌شده')
                    ->trueLabel('خوانده شده')
                    ->falseLabel('خوانده نشده'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->after(fn (ContactMessage $record) => $record->update(['is_read' => true])),
                Action::make('markAsRead')
                    ->label('خوانده شد')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->hidden(fn (ContactMessage $record) => $record->is_read)
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => true])),
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
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
