<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Faqs;

use Reyhan\Core\Filament\Resources\Faqs\Pages\CreateFaq;
use Reyhan\Core\Filament\Resources\Faqs\Pages\EditFaq;
use Reyhan\Core\Filament\Resources\Faqs\Pages\ListFaqs;
use Reyhan\Core\Models\Faq;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'پرسش‌های متداول (FAQ)';

    protected static ?string $modelLabel = 'پرسش متداول';

    protected static ?string $pluralModelLabel = 'پرسش‌های متداول';

    protected static string|UnitEnum|null $navigationGroup = 'محتوا و اطلاع‌رسانی';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(['default' => 3, 'lg' => 2])
                            ->schema([
                                Section::make('محتوای پرسش و پاسخ')
                                    ->schema([
                                        TextInput::make('question')
                                            ->label('عنوان پرسش')
                                            ->placeholder('مثال: سفارش من چه زمانی تحویل داده خواهد شد؟')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('category')
                                            ->label('دسته‌بندی پرسش')
                                            ->placeholder('مثال: ارسال و تحویل، مرجوعی، اصالت کالا')
                                            ->datalist([
                                                'اصالت کالا و گارانتی',
                                                'سفارش و تحویل',
                                                'هزینه‌ها و تخفیف',
                                                'مرجوعی و انصراف',
                                                'حساب کاربری و ثبت‌نام',
                                                'پرداخت و فاکتور',
                                            ])
                                            ->maxLength(100),

                                        Textarea::make('answer')
                                            ->label('پاسخ کامل')
                                            ->placeholder('متن شفاف و توضیحات راهنما برای مشتری...')
                                            ->required()
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Grid::make(1)
                            ->columnSpan(['default' => 3, 'lg' => 1])
                            ->schema([
                                Section::make('تنظیمات نمایش')
                                    ->schema([
                                        TextInput::make('order')
                                            ->label('ترتیب اولویت نمایش')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('عدد کمتر = اولویت نمایش بالاتر در سایت'),

                                        Toggle::make('is_active')
                                            ->label('فعال و قابل نمایش')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('عنوان پرسش')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('category')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order')
                    ->label('ترتیب')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('category')
                    ->label('فیلتر دسته‌بندی')
                    ->options(fn () => Faq::query()->whereNotNull('category')->distinct()->pluck('category', 'category')->toArray()),

                TernaryFilter::make('is_active')
                    ->label('وضعیت فعال/غیرفعال'),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}
