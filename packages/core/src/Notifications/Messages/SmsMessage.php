<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Messages;

class SmsMessage
{
    /**
     * @param  array<string, string>  $tokens
     */
    public function __construct(
        public ?string $content = null,
        public ?string $otpCode = null,
        public array $tokens = [],
        public ?string $to = null,
    ) {}

    public static function create(?string $content = null): self
    {
        return new self(content: $content);
    }

    public function to(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function otp(string $code, array $tokens = []): self
    {
        $this->otpCode = $code;
        $this->tokens = $tokens;

        return $this;
    }

    public function isOtp(): bool
    {
        return $this->otpCode !== null;
    }
}
