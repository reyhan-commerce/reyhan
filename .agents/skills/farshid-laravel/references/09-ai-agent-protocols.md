# 09 — AI Agent Protocols & Workflow Engine

## 1. Phase 1: Pre-Flight Inspection Protocol
Before writing any code for a non-trivial feature, the AI agent **MUST** perform an automated reconnaissance:

1. **Read `composer.json`**:
   - Determine the exact PHP version (e.g. `^8.3`).
   - Determine the exact Laravel version (e.g. `^11.0`, `^12.0`, `^13.0`).
   - Check installed packages (Pest, Larastan, Sanctum, Cashier, etc.).
2. **Scan Existing Architecture**:
   - Check directory conventions (e.g. `app/Actions`, `app/Services`, `app/Data`).
   - Inspect existing Models, Enums, and FormRequests for styling consistency.
   - Inspect `tests/` structure (Pest vs PHPUnit conventions).
3. **Never Assume Architecture**:
   - Do not invent namespaces or file structures before verifying the repository's existing conventions.

---

## 2. Phase 2: Architecture Proposal Protocol
Before implementing any significant architectural change or new domain feature, the AI must provide a structured **Architecture Proposal** and wait for user approval:

### Proposal Structure:
1. **Requirement Analysis**: Concise statement of what the feature actually needs to accomplish.
2. **Existing Architecture**: Which existing models, services, or actions are affected.
3. **Proposed Architecture**:
   - Controller (thin HTTP endpoint)
   - Form Request (structural validation)
   - Action (business use case with `execute()`)
   - DTO (strongly typed input data)
   - Model & Migration (casts, guarded, foreign keys)
   - Policy (authorization)
   - Events & Jobs (if asynchronous)
4. **Risks Analysis**:
   - Transaction boundaries
   - Concurrency & race condition risks
   - Idempotency & retry risks
   - Performance & N+1 query risks
5. **Implementation Plan & Files To Create/Modify**: Exact list of files.
6. **Testing Plan**: Specific Pest Feature and Unit tests to be added.

---

## 3. Phase 3: Impact Analysis Before Modifying Code
Before modifying any existing Action, Service, or Model:
1. **Grep for all usages**:
   - Controllers invoking the class
   - Queued Jobs and Listeners
   - Artisan Commands
   - Existing automated tests
2. **Preserve Backward Compatibility**: Ensure existing callers do not break when adding new parameters. Use optional parameters or DTO enhancements.

---

## 4. Phase 4: AI Self-Review Rubric
After generating or modifying code, the AI must self-audit against this checklist:
- [ ] Did I introduce a Repository? *(If yes, delete it immediately and use Eloquent)*.
- [ ] Is there an external HTTP call inside a `DB::transaction`? *(If yes, extract it outside)*.
- [ ] Are all parameters and return types strictly typed?
- [ ] Is every new Action declared `final` with an `execute()` method?
- [ ] Are models configured with `protected function casts(): array` and `protected $guarded = ['id']`?
- [ ] Is there any N+1 query risk?
- [ ] Did I write realistic Pest tests?

---

## 5. Phase 5: Verification & Final Report
1. **Execute Real Commands**:
   - Run `php artisan test --filter=...` or `composer test`.
   - Run `php artisan pint` or `./vendor/bin/pint`.
   - Run static analysis if configured (`./vendor/bin/phpstan`).
2. **Report Actual Results**:
   - Never hallucinate or assume tests passed without inspecting terminal output.
   - Present a concise final report:
     - **Changed**: Summary of what was accomplished.
     - **Created Files**: Clickable markdown links to new files.
     - **Modified Files**: Clickable links to modified files.
     - **Architecture Rationale**: Why this approach was chosen.
     - **Test Results**: Actual pass/fail output from terminal commands.
