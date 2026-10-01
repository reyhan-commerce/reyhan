<?php

declare(strict_types=1);

namespace App\Contracts\Models;

/**
 * Contract for Reyhan Order Model.
 */
interface OrderContract
{
    public static function generateOrderNumber(): string;
}
