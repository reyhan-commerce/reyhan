<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     *
     * @var array<string>
     */
    protected const SUPPORTED_LOCALES = ['fa', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        App::setLocale($locale);

        $response = $next($request);

        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    /**
     * Determine the preferred locale from request.
     */
    protected function determineLocale(Request $request): string
    {
        // 1. Query parameter (?lang=fa / ?locale=en)
        $queryLocale = $request->query('lang') ?? $request->query('locale');
        if (is_string($queryLocale) && in_array(strtolower($queryLocale), self::SUPPORTED_LOCALES, true)) {
            return strtolower($queryLocale);
        }

        // 2. Accept-Language header (RFC 9110 compliant with q-factor negotiation)
        $header = $request->header('Accept-Language');
        if (is_string($header) && $header !== '' && $header !== 'en-us,en;q=0.5') {
            $preferred = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);
            if ($preferred && in_array($preferred, self::SUPPORTED_LOCALES, true)) {
                return $preferred;
            }

            $primary = strtolower(substr(trim(explode(',', $header)[0]), 0, 2));
            if (in_array($primary, self::SUPPORTED_LOCALES, true)) {
                return $primary;
            }
        }

        // 3. Fallback to default configured locale (fa)
        return (string) config('app.locale', 'fa');
    }
}
