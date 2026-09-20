<?php

declare(strict_types=1);

namespace App\Pipelines\Normalizer;

use App\Pipelines\Normalizer\Contracts\NormalizerPipeInterface;
use App\Pipelines\Normalizer\Pipes\NormalizeCharactersPipe;
use App\Pipelines\Normalizer\Pipes\NormalizeDigitsPipe;
use App\Pipelines\Normalizer\Pipes\NormalizeZwnjPipe;

class PersianNormalizer
{
    /**
     * Default pipe list for Persian general text normalization.
     *
     * @var list<class-string<NormalizerPipeInterface>>
     */
    protected static array $textPipes = [
        NormalizeCharactersPipe::class,
        NormalizeDigitsPipe::class,
        NormalizeZwnjPipe::class,
    ];

    /**
     * Normalize general Persian text (titles, descriptions, attributes, names).
     */
    public static function normalizeText(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $carry = $value;
        foreach (self::$textPipes as $pipeClass) {
            $pipe = new $pipeClass;
            $carry = $pipe->handle($carry, fn (string $res): string => $res);
        }

        return $carry;
    }

    /**
     * Normalize text specifically for search queries (cleans characters and ZWNJ, preserves numbers).
     */
    public static function normalizeSearchQuery(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $searchPipes = [
            NormalizeCharactersPipe::class,
            NormalizeZwnjPipe::class,
        ];

        $carry = $value;
        foreach ($searchPipes as $pipeClass) {
            $pipe = new $pipeClass;
            $carry = $pipe->handle($carry, fn (string $res): string => $res);
        }

        return $carry;
    }

    /**
     * Convert any numeric string (including Persian/Arabic numerals) to clean ASCII digits.
     */
    public static function normalizeNumber(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $ascii = NormalizeDigitsPipe::toAscii($value);

        return (string) preg_replace('/[^\d.]/', '', $ascii);
    }

    /**
     * Normalize Iranian mobile phone number to standard 11-digit format (09xxxxxxxxx).
     */
    public static function normalizeMobile(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $digits = NormalizeDigitsPipe::toAscii($value);
        $clean = (string) preg_replace('/\D/', '', $digits);

        // Convert international format: +989... or 00989... or 989... to 09...
        if (str_starts_with($clean, '00989')) {
            return '0'.substr($clean, 4);
        }

        if (str_starts_with($clean, '989')) {
            return '0'.substr($clean, 2);
        }

        if (str_starts_with($clean, '9') && strlen($clean) === 10) {
            return '0'.$clean;
        }

        return $clean;
    }

    /**
     * Normalize Iranian National Code (Melli code) to 10-digit ASCII string with leading zeros.
     */
    public static function normalizeNationalCode(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $digits = NormalizeDigitsPipe::toAscii($value);
        $clean = (string) preg_replace('/\D/', '', $digits);

        if ($clean !== '' && strlen($clean) < 10) {
            $clean = str_pad($clean, 10, '0', STR_PAD_LEFT);
        }

        return $clean;
    }
}
