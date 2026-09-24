# Architecture Proposal: [Feature Name]

## 1. Requirement Analysis
- **Goal**: [Clear, unambiguous summary of what needs to be accomplished]
- **Target Audience / Trigger**: [API endpoint, Webhook, Scheduled command, etc.]
- **Key Constraints**: [Security, performance, rate limiting, permissions]

## 2. Existing Architecture Context
- **PHP Version**: [e.g. 8.3 / 8.4]
- **Laravel Version**: [e.g. 11.x / 12.x / 13.x]
- **Affected Domain Concepts**: [Existing models, enums, or tables]
- **Conventions Detected**: [Actions vs Services, Pest vs PHPUnit]

## 3. Proposed Architecture
- **Controller**: `App\Http\Controllers\Api\V1\[Name]Controller` (Thin HTTP delegator)
- **Form Request**: `App\Http\Requests\[Domain]\[Name]Request` (Input validation)
- **DTO**: `App\Data\[Domain]\[Name]Data` (final readonly contract)
- **Action**: `App\Actions\[Domain]\[Name]Action` (final class with `execute()`)
- **Service**: [Only if multiple cohesive operations share domain context]
- **Model / Migration**: `App\Models\[Name]` (casts, relationships, domain methods, guarded)
- **Enum**: `App\Enums\[Name]Enum` (Backed string/int with uppercase cases)
- **Policy**: `App\Policies\[Name]Policy` (Model authorization)
- **Queue / Job**: `App\Jobs\[Name]Job` (If asynchronous with `$tries` and `$backoff`)

## 4. Risks & Resilience Analysis
- **Transaction Boundary**: [Where DB::transaction starts and ends; confirm NO external HTTP calls inside]
- **Concurrency & Race Conditions**: [Database unique constraints, lockForUpdate, or Cache::lock]
- **Idempotency**: [Business invariant check to guarantee safe retries]
- **Performance & N+1**: [Eager loading, selective columns, index strategy]

## 5. Implementation Steps & Files
### Files To Create:
- `app/Actions/...`
- `app/Data/...`
- `app/Http/Requests/...`
- `tests/Feature/Actions/...`

### Files To Modify:
- `routes/api.php`

## 6. Testing Plan (Pest)
- [ ] Happy path test verifying state change and database records.
- [ ] Validation failure test verifying 422 HTTP responses.
- [ ] Authorization failure test verifying 403 Forbidden.
- [ ] Idempotency test (calling the action twice produces safe outcome).
