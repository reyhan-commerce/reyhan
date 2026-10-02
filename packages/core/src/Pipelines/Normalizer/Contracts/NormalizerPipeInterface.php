<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Normalizer\Contracts;

use Closure;

interface NormalizerPipeInterface
{
    /**
     * Handle the payload through the pipe.
     *
     * @param  Closure(string): string  $next
     */
    public function handle(string $content, Closure $next): string;
}
