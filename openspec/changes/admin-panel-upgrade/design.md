# Design Decisions: Filament 5 Admin Panel UX/UI & Comprehensive Resource Upgrade

## Architectural Strategy
- **Filament Tabs Component**: Use `Filament\Schemas\Components\Tabs` (or `Filament\Forms\Components\Tabs`) in `ManageSettings` to group fields cleanly without vertical clutter.
- **Navigation Groups**: Configure `getNavigationGroup()` across resources using standard Persian translations:
  - `فروشگاه و کاتالوگ`: Products, Categories, Brands
  - `سفارشات و مالی`: Orders, Payments, Coupons
  - `مشتریان و بازخورد`: Users, Addresses, Reviews
  - `دسترسی و امنیت`: Admins, Roles & Permissions
  - `تنظیمات سیستم`: ManageSettings, Backups
- **Currency Display**: All price values in tables and infolists formatted in Toman with localized Persian number separators.
- **Infolists**: Implement `infolist()` on `OrderResource` using Filament 5 Infolist schemas for an invoice-style view.
- **Code Quality**: Strict PHP 8.5 type declarations, passing Pint style checks and Larastan Level 8 static analysis.
