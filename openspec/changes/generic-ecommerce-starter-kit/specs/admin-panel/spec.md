# Spec Delta: Admin Control Panel & Store Settings

## ADDED Requirements

### Requirement: Dynamic Theme & Brand Configuration
The admin panel MUST provide a dedicated theme management interface within Spatie Settings allowing real-time customization of visual design tokens.

#### Scenario: Update Visual Theme Tokens
- **WHEN** SuperAdmin adjusts primary color hex, secondary color, border-radius slider, spacing scale, shadow intensity, or backdrop-blur in Filament
- **THEN** system saves settings into `ThemeSettings` schema and clears cached theme payload in Redis.

#### Scenario: Upload Store Logo and Favicon
- **WHEN** staff uploads light/dark logo or favicon assets in theme settings
- **THEN** media assets are stored via Spatie Media Library and exposed in public settings endpoints.

### Requirement: Administrative Feature Flag Management
The admin panel SHALL provide an interactive toggle dashboard for managing platform feature flags.

#### Scenario: Toggle Feature State from Filament
- **WHEN** staff turns off `coupons` or `reviews` toggle switch in admin panel
- **THEN** system updates Pennant state in database and reflects state across the application immediately.
