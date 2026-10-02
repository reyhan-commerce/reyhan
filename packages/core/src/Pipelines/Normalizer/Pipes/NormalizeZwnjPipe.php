<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Normalizer\Pipes;

use Reyhan\Core\Pipelines\Normalizer\Contracts\NormalizerPipeInterface;
use Closure;

final class NormalizeZwnjPipe implements NormalizerPipeInterface
{
    /**
     * Handle the payload through the pipe.
     *
     * @param  Closure(string): string  $next
     */
    public function handle(string $content, Closure $next): string
    {
        // 1. Replace multiple ZWNJ with a single ZWNJ (\u{200c})
        $normalized = (string) preg_replace('/(\x{200C})+/u', "\u{200c}", $content);

        // 2. Remove ZWNJ adjacent to standard spaces or at word boundaries
        $normalized = (string) preg_replace('/[\s\x{200C}]*\x{200C}\s+/u', ' ', $normalized);
        $normalized = (string) preg_replace('/\s+\x{200C}[\s\x{200C}]*/u', ' ', $normalized);

        // 3. Remove ZWNJ from the very beginning or end of string
        $normalized = (string) preg_replace('/^\x{200C}+|\x{200C}+$/u', '', $normalized);

        // 4. Collapse multiple whitespaces into a single space
        $normalized = (string) preg_replace('/[ \t]+/u', ' ', $normalized);

        // 5. Trim standard and unicode whitespace
        $normalized = trim($normalized);

        return $next($normalized);
    }
}
