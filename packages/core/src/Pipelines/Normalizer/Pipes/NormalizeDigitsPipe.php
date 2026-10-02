<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Normalizer\Pipes;

use Reyhan\Core\Pipelines\Normalizer\Contracts\NormalizerPipeInterface;
use Closure;

final class NormalizeDigitsPipe implements NormalizerPipeInterface
{
    /**
     * Arabic-Indic to ASCII map.
     *
     * @var array<string, string>
     */
    protected static array $arabicToAscii = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * Persian to ASCII map.
     *
     * @var array<string, string>
     */
    protected static array $persianToAscii = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    /**
     * ASCII to Persian map.
     *
     * @var array<int|string, string>
     */
    protected static array $asciiToPersian = [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ];

    /**
     * Convert any Arabic or English numerals in text to Persian numerals.
     */
    public static function toPersian(string $content): string
    {
        $ascii = strtr($content, self::$arabicToAscii);

        return strtr($ascii, self::$asciiToPersian);
    }

    /**
     * Convert any Persian or Arabic numerals to standard ASCII numerals.
     */
    public static function toAscii(string $content): string
    {
        $step1 = strtr($content, self::$arabicToAscii);

        return strtr($step1, self::$persianToAscii);
    }

    /**
     * Handle the payload through the pipe (default text normalization to Persian numerals).
     *
     * @param  Closure(string): string  $next
     */
    public function handle(string $content, Closure $next): string
    {
        $normalized = self::toPersian($content);

        return $next($normalized);
    }
}
