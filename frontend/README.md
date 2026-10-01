# Reyhan Commerce — Frontend Core (Nuxt 4)

Production-grade, ultra-accessible, mobile-first headless storefront for any e-commerce vertical powered by the Reyhan Commerce framework. Built on **Nuxt 4**, **Nuxt UI (Reka UI)**, **Tailwind CSS v4**, and **Vazirmatn** typography.

---

## 🛠️ Technology Stack & Foundations

| Layer | Component / Specification |
| :--- | :--- |
| **Framework** | Nuxt 4.x (SSR enabled, `app/` directory structure) |
| **UI Component Library** | Nuxt UI (latest stable with Reka UI primitives) |
| **CSS Engine** | Tailwind CSS v4 (`@import "tailwindcss";` and `@theme`) |
| **Styling Rule** | **Zero Custom CSS** — Strictly pure Tailwind utility classes |
| **Typography** | Vazirmatn Variable (`@fontsource-variable/vazirmatn`) |
| **Directionality** | 100% Right-to-Left (RTL) (`dir="rtl"`, `lang="fa-IR"`) |
| **State Management** | Pinia + `pinia-plugin-persistedstate` |
| **Composables** | `@vueuse/nuxt` & `@vueuse/core` |
| **Media & Images** | `@nuxt/image` with IPX / Sharp optimization |
| **SEO & Schema.org** | `@nuxtjs/seo` (automated OpenGraph, Sitemap, Schema.org) |
| **Package Manager** | `pnpm` v12.x |
| **Language & Typing** | TypeScript with strict checking |

---

## 🚀 Quickstart & Development

### 1. Prerequisites
- Node.js v22+ (tested on Node v24)
- `pnpm` v12+

### 2. Installation
```bash
cd frontend
pnpm install
```

### 3. Environment Variables
Configure `.env` (or copy `.env.example`):
```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1
```

### 4. Running Dev Server
```bash
pnpm dev
```
The application will be available at `http://localhost:3000`.

---

## 🔐 Authentication & API Integration (Phase 1)

- **`useApi()` Composable** (`app/composables/useApi.ts`): Type-safe `$fetch` client with automatic base URL injection, Bearer token attachment from Cookie/Pinia, and RFC 7807 toast alerts.
- **`useAuthStore`** (`app/stores/auth.ts`): Pinia store with full customer OTP lifecycle, 30-day SSR cookie persistence, and auth modal trigger.
- **`CaptchaInput` Component** (`app/components/auth/CaptchaInput.vue`): Interactive SVG captcha with one-click reload button.
- **`AuthModal` Component** (`app/components/auth/AuthModal.vue`): Accessible 2-step OTP modal (mobile input + 5-digit verification code with 120s countdown).
- **`auth` Route Middleware** (`app/middleware/auth.ts`): Client & SSR protection for user-only routes.

---

## 🧪 Quality Assurance Commands

Ensure code cleanliness and type correctness prior to pushing commits:

```bash
# 1. Run ESLint checks
pnpm lint

# 2. Auto-fix linting issues
pnpm lint --fix

# 3. Run Nuxt typecheck (Vue & TS)
pnpm typecheck

# 4. Production build test
pnpm build
```

---

## 📂 Architecture Reference

For detailed component rules, Universal Design & Accessibility (Elderly + Children), state management patterns, and AI agent developer instructions, see [`ARCHITECTURE.md`](./ARCHITECTURE.md).
