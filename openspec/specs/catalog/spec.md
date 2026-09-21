# Product Catalog, Taxonomy & Variant Matrix Specification

## Purpose
Governs the multi-tier cosmetic product catalog, recursive category tree, brands, attribute-variant taxonomy, real-time Persian slug generation, and PostgreSQL trigram typo-tolerant search.

## Requirements

### Requirement: Recursive Category Hierarchy
Categories SHALL support unlimited self-referencing nesting with ordering, icons, and media attachments.

#### Scenario: Fetch Category Tree
- **WHEN** client invokes `GET /api/v1/categories/tree`
- **THEN** system returns hierarchical JSON tree structure with child categories and product counts, cached in Redis.

### Requirement: Product-Variant-Attribute Matrix
Parent products MUST encapsulate purchasable variants (`ProductVariant`) defined by attributes (e.g. Color with Hex, Size, Finish, Volume, SPF).

#### Scenario: Retrieve Product Detail
- **WHEN** client requests a product by decoded Persian slug via `GET /api/v1/products/{slug}`
- **THEN** system responds with product details, media gallery, scoped attributes, and the valid `available_variants` matrix including stock counts and prices in Rials/Tomans.

#### Scenario: Prevent Non-Existent Attribute Combinations
- **WHEN** user selects attribute values in frontend `VariantSelector.vue`
- **THEN** system evaluates valid combinations from `available_variants`, disabling invalid combinations and marking out-of-stock options.

### Requirement: Real-Time Persian Slug Generation
Filament admin panel MUST generate URL-safe Persian slugs live as the administrative user types product names.

#### Scenario: Generate Unique Persian Slug
- **WHEN** administrator inputs or updates a product title in Filament
- **THEN** system automatically generates a slug, verifies uniqueness in the database, and appends a numeric suffix if collision occurs.

### Requirement: Typo-Tolerant Search (PostgreSQL pg_trgm)
Product discovery SHALL support fuzzy matching and typo-tolerance for Persian search queries.

#### Scenario: Search Products with Typos
- **WHEN** user searches `GET /api/v1/products?search=ریمل` with slight misspelling or variation
- **THEN** PostgreSQL `pg_trgm` GIN index calculates trigram similarity score and returns relevant matching catalog results.
