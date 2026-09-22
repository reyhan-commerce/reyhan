# Spec Delta: Industry Presets Command

## Purpose

Provides automated CLI scaffolding of domain-specific e-commerce fixtures, taxonomies, and attribute definitions across varied industry verticals.

## ADDED Requirements

### Requirement: Artisan Industry Preset Scaffolding
The system SHALL provide an artisan command `shop:preset` accepting industry vertical identifiers to configure the store instance.

#### Scenario: Seed Apparel Preset
- **WHEN** developer runs `php artisan shop:preset apparel`
- **THEN** system populates database with clothing categories, fashion brands, and attributes (Size, Color, Fabric Material, Fit Type) with sample variants.

#### Scenario: Seed Digital Preset
- **WHEN** developer runs `php artisan shop:preset digital`
- **THEN** system populates database with electronic categories, tech brands, and technical attributes (Storage, RAM, Color, Warranty Period) with sample variants.

#### Scenario: Clean Blank Store Setup
- **WHEN** developer runs `php artisan shop:preset blank`
- **THEN** system wipes dummy catalog records while preserving administrative users, geographic provinces, and baseline system settings.
