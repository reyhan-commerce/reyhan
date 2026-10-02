<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Admins;

use Reyhan\Core\Filament\Resources\Admins\Pages\CreateAdmin;
use Reyhan\Core\Filament\Resources\Admins\Pages\EditAdmin;
use Reyhan\Core\Filament\Resources\Admins\Pages\ListAdmins;
use Reyhan\Core\Models\Admin;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'مدیران و کارمندان';

    protected static ?string $modelLabel = 'مدیر سامانه';

    protected static ?string $pluralModelLabel = 'مدیران و کارمندان';

    protected static string|UnitEnum|null $navigationGroup = 'دسترسی و پرسنل';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات حساب کاربری مدیر / پرسنل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام و نام خانوادگی')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('آدرس ایمیل')
                            ->email()
                            ->required()
                            ->unique(Admin::class, 'email', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('رمز عبور')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Select::make('roles')
                            ->label('نقش‌های دسترسی (Shield Roles)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('حساب فعال است (امکان ورود به پنل)')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('نقش‌های دسترسی')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاریخ تعریف')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d') : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('وضعیت دسترسی')
                    ->placeholder('همه')
                    ->trueLabel('حساب‌های فعال')
                    ->falseLabel('حساب‌های مسدود'),

                TrashedFilter::make(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
