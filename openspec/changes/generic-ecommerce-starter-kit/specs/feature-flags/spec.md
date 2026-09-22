# Spec Delta: Feature Flags Engine

## Purpose

Enables runtime activation and deactivation of e-commerce business capabilities using Laravel Pennant with database persistence, Filament admin controls, and Nuxt frontend exposure.

## ADDED Requirements

### Requirement: Database-Driven Feature Toggling with Laravel Pennant
The backend SHALL define and resolve platform features dynamically using Laravel Pennant backed by a database table.

#### Scenario: Evaluate Active Features
- **WHEN** client requests feature state or backend checks feature authorization
- **THEN** system queries Pennant database driver and returns boolean activation status for features including `reviews`, `coupons`, `wishlist`, `brands`, `stock_alerts`, and `comparison`.

#### Scenario: Administrative Feature Switching
- **WHEN** staff toggles a feature state in Filament admin panel
- **THEN** system immediately updates Pennant feature record in database and flushes cached feature states without code deployment.

### Requirement: Public Features API Endpoint
The backend SHALL expose active feature flags to the frontend through a cached public endpoint.

#### Scenario: Fetch Active Features
- **WHEN** frontend calls `GET /api/v1/app/features`
- **THEN** system returns JSON dictionary mapping feature names to their boolean active states.

### Requirement: Frontend Feature Guard Composable
The frontend SHALL provide a `useFeatures()` composable to conditionally render UI sections and prevent navigation to disabled feature routes.

#### Scenario: Conditional Feature Rendering
- **WHEN** component evaluates `hasFeature('reviews')`
- **THEN** template conditionally renders review section if true, or suppresses review inputs and badges if false.
