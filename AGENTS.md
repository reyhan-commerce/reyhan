# EasyShop Frontend AI Agent Operational Directives

> **CRITICAL INSTRUCTION FOR ALL AI CODING AGENTS**:
> Before writing, generating, or refactoring any code in this repository, you **MUST** read and strictly follow the architectural pillars defined in `ARCHITECTURE.md`. Failure to follow these rules constitutes a violation of project architectural integrity.

---

## 1. Non-Negotiable Frontend Rules

### 1. 100% Native Nuxt UI & Zero Custom CSS
- **STRICT PROHIBITION**: Never write custom CSS classes in `<style>` blocks (e.g., `.my-cart { ... }` is strictly forbidden).
- Every visual element must be constructed using native **Nuxt UI** components (e.g., `<UButton>`, `<UModal>`, `<USlideover>`, `<UCard>`, `<UInput>`, `<UBadge>`) customized strictly via:
  1. Component props (`color`, `variant`, `size`, `icon`, `trailing-icon`).
  2. Tailwind CSS v4 utility classes passed to `class` or component `ui` slots.

### 2. Universal Design & Extreme Usability
- **Minimum Touch Targets**: All interactive elements (buttons, inputs, checkboxes, swatches) must have a touch target of at least **`48x48px`** (`min-h-12 min-w-12`).
- **High Contrast**: Ensure clear legibility in both Light and Dark modes.

### 3. Strict RTL & Persian Typography
- Document root is hardcoded to `dir="rtl"` and `lang="fa-IR"`.
- Use logical CSS utilities (`ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*`) rather than directional (`ml-*`, `mr-*`, `left-*`, `right-*`).
- Font family is **Vazirmatn Variable** loaded via `@fontsource-variable/vazirmatn`.

### 4. Zero-Friction Human Security (In-Browser Proof-of-Work)
- Distortion/math captchas are strictly prohibited.
- Use interactive `<AuthCaptchaCheckbox />` widget utilizing client-side Proof-of-Work (`crypto.subtle.digest('SHA-256')`).
- Provide immediate visual feedback (idle, verifying spinner, green success badge with checkmark, retry on error).

---

## 2. Quality Verification Commands
Before completing your turn, you must run:
1. `pnpm lint` (Must pass with 0 errors and 0 warnings)
2. `pnpm typecheck` (Must pass with 0 errors)
3. `pnpm build` (Production build must succeed)
