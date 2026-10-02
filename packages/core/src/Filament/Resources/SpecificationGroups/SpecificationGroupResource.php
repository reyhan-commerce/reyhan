<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\SpecificationGroups;

use Reyhan\Core\Filament\Resources\SpecificationGroups\Pages\CreateSpecificationGroup;
use Reyhan\Core\Filament\Resources\SpecificationGroups\Pages\EditSpecificationGroup;
use Reyhan\Core\Filament\Resources\SpecificationGroups\Pages\ListSpecificationGroups;
use Reyhan\Core\Models\SpecificationGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SpecificationGroupResource extends Resource
{
    protected static ?string $model = SpecificationGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'مشخصات فنی کالا';

    protected static ?string $modelLabel = 'گروه مشخصات فنی';

    protected static ?string $pluralModelLabel = 'مشخصات فنی کالا';

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه و کاتالوگ';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('گروه مشخصات فنی')
                    ->description('عنوان گروه مشخصات (مانند: مشخصات عمومی، سخت‌افزار، دوربین)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام گروه')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('مشخصات زیرمجموعه این گروه')
                    ->description('اقلام و پارامترهای فنی که زیرمجموعه این گروه سنجیده می‌شوند')
                    ->schema([
                        Repeater::make('specifications')
                            ->relationship('specifications')
                            ->label('لیست مشخصات')
                            ->columns(4)
                            ->schema([
                                TextInput::make('name')
                                    ->label('عنوان مشخصه')
                                    ->placeholder('مثلاً: حافظه رم')
                                    ->required(),
                                TextInput::make('unit')
                                    ->label('واحد سنجش')
                                    ->placeholder('مثلاً: گیگابایت'),
                                Select::make('type')
                                    ->label('نوع فیلد')
                                    ->options([
                                        'text' => 'متنی ساده',
                                        'number' => 'عددی',
                                        'select' => 'انتخابی',
                                        'boolean' => 'بله / خیر',
                                    ])
                                    ->default('text')
                                    ->required(),
                                Toggle::make('is_filterable')
                                    ->label('قابل فیلتر در کاتالوگ')
                                    ->default(false),
                            ])
                            ->orderColumn('order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام گروه مشخصات')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('specifications_count')
                    ->counts('specifications')
                    ->label('تعداد مشخصات')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('order')
                    ->label('ترتیب')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListSpecificationGroups::route('/'),
            'create' => CreateSpecificationGroup::route('/create'),
            'edit' => EditSpecificationGroup::route('/{record}/edit'),
        ];
    }
}
