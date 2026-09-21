# Spec Delta: Modern Enterprise UI/UX Refinement for Admin Panel

## Purpose
Establishes modern e-commerce UI/UX standards across Filament 5 forms, tables, and infolists, including main/sidebar form layouts, consolidated table columns, and invoice-grade infolists.

## ADDED Requirements

### Requirement: Main and Sidebar Form Grid Architecture
Product and catalog management forms MUST utilize a responsive 3-column or 2-column grid partitioning primary content from metadata and publishing controls.

#### Scenario: Edit Product Form Layout
- **WHEN** staff navigates to create or edit a product in `ProductResource`
- **THEN** system renders primary content (title, descriptions, media gallery) in a 2-column wide canvas, and classification/pricing/status controls in a dedicated 1-column sidebar.

### Requirement: Consolidated and Rich Table Columns
Resource tables SHALL consolidate related attributes into multi-line cells with clear typographic hierarchy to minimize horizontal scrolling.

#### Scenario: Display Product and Order Table Rows
- **WHEN** staff views products or orders table
- **THEN** system renders thumbnails with title and slug/SKU in a unified column, and buyer name with mobile number in a unified column, with currency values clearly formatted in Toman.

### Requirement: Enhanced Table Empty States and Filters
Tables MUST provide contextual empty states with Persian instructions and structured filter forms.

#### Scenario: View Empty Table or Complex Filters
- **WHEN** a table has no matching records or filters are opened
- **THEN** system displays custom Persian empty state messages with Heroicons and renders filter options in a clean 2-column modal layout.
