# Spec Delta: Search Engine Optimization, AI Discovery & Persian UI

## ADDED Requirements

### Requirement: Dynamic CSS Variable Theming
The frontend MUST apply visual design tokens provided by the backend settings via root CSS variables during SSR rendering.

#### Scenario: SSR Theme Injection
- **WHEN** client requests any frontend page via SSR
- **THEN** server injects inline style block containing active `--ui-color-primary`, `--ui-radius`, `--theme-spacing`, and `--theme-font-scale` into `:root` preventing flash of unstyled content (FOUC).

### Requirement: Universal Skeleton Loading States
All dynamic sections of the frontend MUST render designated skeleton components while data is fetching.

#### Scenario: Catalog and PDP Skeleton States
- **WHEN** catalog grid or product detail page is waiting for API response
- **THEN** Nuxt UI `<USkeleton>` representations of product cards, swatches, and summaries are displayed in place of empty space or spinners.

### Requirement: Accessible Data Refresh Actions
Key interface surfaces MUST offer explicit refresh actions to re-fetch stale data.

#### Scenario: Trigger Manual Catalog and Cart Refresh
- **WHEN** user clicks refresh button on catalog toolbar or cart drawer
- **THEN** system re-fetches latest products or reconciles stock levels and shows temporary loading spin indicator.

### Requirement: Bilingual Digit Normalization on Form Inputs
All numeric and code form fields in the user interface MUST accept both Persian (۰-۹) and English (0-9) digits without validation rejection.

#### Scenario: Input Persian Mobile or Postal Code
- **WHEN** user types Persian numerals into phone, national code, postal code, or quantity inputs
- **THEN** system automatically normalizes characters to standardized ASCII numerals before validation and submission.

### Requirement: Typed Form Validation with Zod
All consumer interactive forms MUST be validated against strict Zod schemas utilizing Nuxt UI `<UForm>`.

#### Scenario: Form Submission with Validation Errors
- **WHEN** user submits form with missing or invalid fields
- **THEN** `<UForm>` renders inline Persian error messages under offending fields without triggering page reload.
