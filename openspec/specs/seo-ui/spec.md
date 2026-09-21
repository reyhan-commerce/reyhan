# Search Engine Optimization, AI Discovery & Persian UI Specification

## Purpose
Governs modular SEO architecture via `@nuxtjs/seo`, Google Rich Snippets (Schema.org JSON-LD), autonomous AI shopping agent discovery files (`llms.txt`), and Persian typography with Vazirmatn FD numeral rendering.

## Requirements

### Requirement: Structured Data & Schema.org Rich Snippets
Product and category pages MUST inject valid JSON-LD schemas for search engines and social platforms.

#### Scenario: Product Rich Snippets (PDP)
- **WHEN** crawler or browser renders product detail page
- **THEN** system outputs `defineProduct()` with name, description, brand, images, SKU, `defineOffer()` with price currency (IRR/IRT) and availability status, `defineAggregateRating()`, and `defineBreadcrumb()`.

### Requirement: Dynamic Sitemap & Crawler Directives
Headless XML sitemap SHALL synchronize directly with backend product catalog and category slugs.

#### Scenario: Generate Dynamic Sitemap
- **WHEN** search crawler accesses `/sitemap.xml`
- **THEN** `@nuxtjs/sitemap` fetches active URL slugs from `GET /api/v1/sitemap/urls` (cached in Redis) and renders valid XML sitemap with last modified timestamps.

#### Scenario: Robots Directives
- **WHEN** crawler accesses `/robots.txt`
- **THEN** system serves disallow rules for `/admin`, `/checkout`, and `/profile` while allowing full indexing of products and categories.

### Requirement: AI Agent Readiness (llms.txt)
Catalog endpoints and schemas MUST be exposed in standardized markdown format for autonomous LLM shopping agents.

#### Scenario: AI Agent Discovery
- **WHEN** an AI shopping crawler or LLM accesses `/llms.txt` or `/llms-full.txt`
- **THEN** system returns structured markdown describing API endpoints, search syntax, and data schemas for programmatic consumption.

### Requirement: Persian Typography & Numeral Rendering
User interface MUST render authentic Persian digits natively across all prices, stock counts, and dates while isolating Latin identifiers.

#### Scenario: Persian Numeral Rendering
- **WHEN** numeric values (prices, ratings, stock quantities) are rendered in components
- **THEN** Vazirmatn FD font displays native Persian numerals (۰-۹). For Latin-specific strings (SKU, English codes), `.font-en` is applied.
