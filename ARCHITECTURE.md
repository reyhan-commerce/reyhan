# Frontend Architectural Manual & Context (AI & Human Guide)

This document serves as the authoritative blueprint for the frontend architecture, design principles, component guidelines, and developer conventions for the EasyShop e-commerce platform. **All AI coding agents and human engineers must adhere strictly to these rules.**

---

## 1. Core Architectural Pillars

### Pillar 1: 100% Native Nuxt UI & Zero Custom CSS
- **Hard Rule**: Write **NO custom CSS classes** inside `<style>` blocks (e.g., `.my-cart { ... }` is strictly forbidden).
- Every visual element must be constructed using native **Nuxt UI** components (e.g., `<UButton>`, `<UModal>`, `<USlideover>`, `<UCard>`, `<UInput>`, `<UBadge>`) customized exclusively through:
  1. Component props (`color`, `variant`, `size`, `icon`, `trailing-icon`).
  2. Tailwind CSS v4 utility classes passed to the `class` prop or component `ui` slots.

### Pillar 2: Universal Design & Extreme Usability (Elderly & Children)
- **Minimum Touch Targets**: All interactive elements (buttons, quantity selectors, swatches, menu items) must have a touch target of at least **`48x48px`** (`min-h-12 min-w-12`).
- **High Visual Contrast**: Maintain minimum 4.5:1 text-to-background contrast ratio across both Light and Dark themes.
- **Cognitive Simplicity**: Large, legible Shamsi pricing with Persian number formatting, clear icon + text labels, and immediate visual feedback.

### Pillar 3: Strict RTL & Persian Typography
- Document root is hardcoded to `dir="rtl"` and `lang="fa-IR"`.
- Use logical CSS utilities (`ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*`) rather than directional (`ml-*`, `mr-*`, `left-*`, `right-*`).
- Font family is **Vazirmatn Variable** loaded via `@fontsource-variable/vazirmatn`.

---

## 2. Directory Structure & Nuxt 4 Conventions

The frontend codebase is organized according to the Nuxt 4 directory convention:

```
frontend/
├── app/
│   ├── assets/
│   │   └── css/
│   │       └── main.css      # Tailwind v4 import & theme configuration
│   ├── components/
│   │   ├── cart/             # Slide-over cart drawer & optimistic cart line items
│   │   ├── catalog/          # Product card, filter drawer, sort toolbar
│   │   ├── checkout/         # Multi-step checkout & address picker
│   │   ├── layout/           # Header, mobile bottom navigation, footer
│   │   └── product/          # Gallery, scoped variant matrix selector, stock status
│   ├── composables/
│   │   ├── useApi.ts         # Centralized typed HTTP client ($fetch wrapper)
│   │   ├── useCart.ts        # Cart actions & optimistic updates
│   │   └── usePersian.ts     # Persian digits, currency formatting (Toman), Jalali dates
│   ├── layouts/
│   │   ├── default.vue       # Main store layout with Header, Footer, and Slide-over Cart
│   │   └── auth.vue          # Minimal clean layout for OTP verification
│   ├── middleware/
│   │   └── auth.ts           # Route guard checking active Sanctum auth token
│   ├── pages/
│   │   ├── index.vue         # Homepage (hero banners, flash deals, categories)
│   │   ├── products/
│   │   │   ├── index.vue     # Catalog listing with filters & trigram search
│   │   │   └── [slug].vue    # Product detail page (PDP) with live Persian slugs
│   │   ├── categories/
│   │   │   └── [slug].vue    # Category-specific listing
│   │   ├── checkout/         # Checkout multi-step flow & callback
│   │   └── profile/          # Customer profile & order history
│   ├── stores/
│   │   ├── auth.ts           # Sanctum token & user profile store
│   │   ├── cart.ts           # Persistent cart store with cookie sync
│   │   └── catalog.ts        # Filters, search query, and category state
│   ├── app.config.ts         # Nuxt UI theme overrides
│   └── app.vue               # Root application template with <UApp>
├── nuxt.config.ts            # Nuxt 4 modules, SEO, and runtime config
├── package.json
└── tsconfig.json
```

---

## 3. Data Fetching & API Communication (`useApi`)

- Never use raw `fetch()` or `axios`.
- Use a dedicated `useApi` composable wrapping `useFetch` / `$fetch`:
  - Automatically prepends `NUXT_PUBLIC_API_BASE`.
  - Automatically attaches `Authorization: Bearer <sanctum_token>` if user is authenticated.
  - Formats errors according to RFC 7807 problem details and triggers Persian toast alerts.

---

## 4. Coding Standards & AI Agent Guidelines

1. **Composition API & `<script setup lang="ts">`**:
   - Always use Vue 3 `<script setup lang="ts">`.
   - Explicitly type all props, emits, and composables.
2. **Quality Gates Before Commit**:
   - Run `pnpm lint` and ensure 0 errors.
   - Run `pnpm typecheck` and ensure type check passes.
   - Run `pnpm build` to confirm production bundle builds without errors.
