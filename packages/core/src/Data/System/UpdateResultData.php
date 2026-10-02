<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\System;

use Spatie\LaravelData\Data;

final class UpdateResultData extends Data
{
    /**
     * @param  list<string>  $logs
     * @param  list<string>  $errors
     */
    public function __construct(
        public bool $success,
        public string $message,
        public array $logs = [],
        public array $errors = [],
        public ?string $duration = null,
    ) {}
}
