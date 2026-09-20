<?php

declare(strict_types=1);

namespace App\Services\Captcha;

use App\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CaptchaService
{
    /**
     * Redis connection name for captcha storage (DB 0).
     */
    protected const REDIS_CONNECTION = 'default';

    /**
     * Captcha TTL in seconds (2 minutes).
     */
    protected const TTL_SECONDS = 120;

    /**
     * Generate a new visual math or alphanumeric SVG captcha.
     *
     * @return array{key: string, svg: string}
     */
    public function generate(): array
    {
        $num1 = random_int(1, 9);
        $num2 = random_int(1, 9);
        $operator = random_int(0, 1) === 1 ? '+' : '-';

        if ($operator === '-' && $num1 < $num2) {
            // Ensure positive result for friendly UX
            [$num1, $num2] = [$num2, $num1];
        }

        $expression = "{$num1} {$operator} {$num2} = ?";
        $result = $operator === '+' ? ($num1 + $num2) : ($num1 - $num2);

        $key = (string) Str::uuid();

        // Save hashed result in Redis DB 0 with 2-minute TTL
        Redis::connection(self::REDIS_CONNECTION)->setex(
            "captcha:{$key}",
            self::TTL_SECONDS,
            (string) $result
        );

        $svg = $this->renderSvg($expression);

        return [
            'key' => $key,
            'svg' => $svg,
        ];
    }

    /**
     * Verify the user's captcha answer and immediately invalidate the key.
     */
    public function verify(?string $key, ?string $answer): bool
    {
        if (empty($key) || empty($answer)) {
            return false;
        }

        $redisKey = "captcha:{$key}";
        $redis = Redis::connection(self::REDIS_CONNECTION);

        /** @var string|null $stored */
        $stored = $redis->get($redisKey);

        // One-time use: immediately delete the key
        $redis->del($redisKey);

        if ($stored === null) {
            return false;
        }

        $normalizedInput = trim(PersianNormalizer::normalizeNumber($answer));

        return $normalizedInput === trim($stored);
    }

    /**
     * Render a lightweight, secure SVG image with noise lines.
     */
    protected function renderSvg(string $text): string
    {
        $width = 140;
        $height = 46;

        // Generate noise lines
        $noiseLines = '';
        for ($i = 0; $i < 4; $i++) {
            $x1 = random_int(0, $width);
            $y1 = random_int(0, $height);
            $x2 = random_int(0, $width);
            $y2 = random_int(0, $height);
            $color = sprintf('#%06X', random_int(0x777777, 0xCCCCCC));
            $noiseLines .= "<line x1='{$x1}' y1='{$y1}' x2='{$x2}' y2='{$y2}' stroke='{$color}' stroke-width='1.5' stroke-dasharray='2,2'/>";
        }

        // Generate characters with slight rotation
        $chars = mb_str_split($text);
        $charSvg = '';
        $xOffset = 18;

        foreach ($chars as $char) {
            $rotate = random_int(-15, 15);
            $y = random_int(28, 33);
            $color = sprintf('#%06X', random_int(0x111111, 0x444444));
            $charSvg .= "<text x='{$xOffset}' y='{$y}' fill='{$color}' font-family='system-ui, sans-serif' font-size='20' font-weight='bold' transform='rotate({$rotate}, {$xOffset}, {$y})'>{$char}</text>";
            $xOffset += 16;
        }

        return "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}' viewBox='0 0 {$width} {$height}' style='background-color: #f8fafc; border-radius: 8px;'>{$noiseLines}{$charSvg}</svg>";
    }
}
