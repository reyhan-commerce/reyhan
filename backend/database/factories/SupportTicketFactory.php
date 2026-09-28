<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketDepartment;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
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
