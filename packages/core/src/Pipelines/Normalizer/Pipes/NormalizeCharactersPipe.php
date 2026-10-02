<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Normalizer\Pipes;

use Reyhan\Core\Pipelines\Normalizer\Contracts\NormalizerPipeInterface;
use Closure;

final class NormalizeCharactersPipe implements NormalizerPipeInterface
{
    /**
     * Replacement map for Arabic to standard Persian letters.
     *
     * @var array<string, string>
     */
    protected array $letterMap = [
        'ك' => 'ک', // Arabic Kaf to Persian Keh
        'ڪ' => 'ک',
        'ي' => 'ی', // Arabic Yeh to Persian Yeh
        'ى' => 'ی',
        'ئ' => 'ئ',
        'ة' => 'ه', // Teh Marbuta to Heh (or ه‌ی based on context)
        'ؤ' => 'و',
        'إ' => 'ا',
        'أ' => 'ا',
        'آ' => 'آ',
    ];

    /**
     * Handle the payload through the pipe.
     *
     * @param  Closure(string): string  $next
     */
    public function handle(string $content, Closure $next): string
    {
        // 1. Replace Arabic letters with standard Persian letters
        $normalized = strtr($content, $this->letterMap);

        // 2. Strip Arabic diacritics / Tashkeel: Fatha, Damma, Kasra, Tanween, Sukun, Shadda
        $normalized = (string) preg_replace('/[\x{064B}-\x{0652}]/u', '', $normalized);

        return $next($normalized);
    }
}
