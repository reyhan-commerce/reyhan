<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\TicketDepartment;
use Reyhan\Core\Enums\TicketPriority;
use Reyhan\Core\Enums\TicketStatus;
use Reyhan\Core\Models\SupportTicket;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticket_number' => 'TCK-'.strtoupper(Str::random(8)),
            'subject' => fake()->sentence(4),
            'department' => TicketDepartment::Support,
            'priority' => TicketPriority::Medium,
            'status' => TicketStatus::Open,
            'order_id' => null,
            'last_reply_at' => now(),
        ];
    }
}
