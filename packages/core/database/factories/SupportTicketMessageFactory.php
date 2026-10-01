<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketMessage>
 */
class SupportTicketMessageFactory extends Factory
{
    protected $model = SupportTicketMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
            'is_staff' => false,
            'attachments' => [],
        ];
    }
}
