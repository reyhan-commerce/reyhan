<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\Response;

final class EnsureFeatureActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $featureName): Response
    {
        if (! Feature::active($featureName)) {
            return new JsonResponse([
                'success' => false,
                'message' => "قابلیت {$featureName} در حال حاضر در فروشگاه غیرفعال است.",
                'error_code' => 'FEATURE_DISABLED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
