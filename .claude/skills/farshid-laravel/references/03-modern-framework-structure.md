# 03 — Modern Framework Structure (Laravel 11, 12 & 13)

## 1. Streamlined Application Architecture
Modern Laravel (11+) eliminated legacy boilerplate files:
- ❌ No `app/Http/Kernel.php`
- ❌ No `app/Console/Kernel.php`
- ❌ No `app/Exceptions/Handler.php`
- ❌ No legacy `RouteServiceProvider` or `EventServiceProvider`

All core framework configuration is consolidated into **`bootstrap/app.php`** and **`bootstrap/providers.php`**.

---

## 2. Configuring `bootstrap/app.php`

### Routing, Middleware, and Exceptions
All routing files, middleware aliases, global middleware, and custom exception handling must be configured directly within `bootstrap/app.php`:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'idempotent' => \App\Http\Middleware\EnsureRequestIsIdempotent::class,
        ]);

        // Append or prepend middleware to web/api groups
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // State-preserving redirects
        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom rendering for API exceptions
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        // Reportable exceptions or integrations (Sentry, etc.)
        $exceptions->report(function (\App\Exceptions\PaymentProcessingException $e) {
            // custom error tracking
        });
    })->create();
```

---

## 3. Service Providers (`bootstrap/providers.php`)
Custom Service Providers are registered in `bootstrap/providers.php` rather than an array in `config/app.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
];
```

---

## 4. Strict Model Enforcement (`AppServiceProvider`)
Always enable strict Eloquent checking in local development and testing to prevent performance traps and hidden data bugs:

```php
namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Prevents lazy loading (N+1), unfillable attribute assignment, and accessing missing attributes
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
```

---

## 5. Modern Concurrency (`Concurrency::run`)
Laravel 11+ introduces the first-class `Concurrency` facade to execute multiple closures concurrently (using processes or forks):

```php
use Illuminate\Support\Facades\Concurrency;

[$userProfile, $orderStats, $inventoryStatus] = Concurrency::run([
    fn () => $profileService->fetchDetails($userId),
    fn () => $orderService->calculateStats($userId),
    fn () => $inventoryService->checkAvailability($productIds),
]);
```
Use `Concurrency::run` when orchestrating independent, read-heavy operations or third-party queries that can be fetched in parallel.

---

## 6. Context Management (`Context` Facade)
Use `Illuminate\Support\Facades\Context` to attach contextual metadata (request ID, tenant ID, user session ID) that automatically flows through all application logs and queued jobs:

```php
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

// In a middleware:
Context::add('correlation_id', (string) Str::uuid());
Context::add('client_ip', $request->ip());

// In logs, jobs, or errors, the correlation_id is automatically attached!
```
