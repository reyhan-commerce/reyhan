<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Enums\TicketDepartment;
use Reyhan\Core\Enums\TicketPriority;
use Reyhan\Core\Enums\TicketStatus;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\SupportTicket;
use Reyhan\Core\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Morilog\Jalali\Jalalian;

final class SupportTicketController extends Controller
{
    /**
     * List user tickets.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tickets = $user->supportTickets()
            ->with('order')
            ->latest('last_reply_at')
            ->paginate((int) $request->query('per_page', 10));

        $data = $tickets->through(fn (SupportTicket $t) => [
            'id' => $t->id,
            'ticket_number' => $t->ticket_number,
            'subject' => $t->subject,
            'department' => $t->department->value,
            'department_label' => $t->department->label(),
            'priority' => $t->priority->value,
            'priority_label' => $t->priority->label(),
            'priority_color' => $t->priority->color(),
            'status' => $t->status->value,
            'status_label' => $t->status->label(),
            'status_color' => $t->status->color(),
            'order_number' => $t->order?->order_number,
            'last_reply_at' => $t->last_reply_at?->toIso8601String(),
            'last_reply_at_jalali' => $t->last_reply_at ? Jalalian::fromCarbon($t->last_reply_at)->format('Y/m/d H:i') : null,
            'created_at' => $t->created_at->toIso8601String(),
            'created_at_jalali' => Jalalian::fromCarbon($t->created_at)->format('Y/m/d H:i'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create a new support ticket with initial message.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'min:3', 'max:255'],
            'department' => ['required', new Enum(TicketDepartment::class)],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['string'],
        ]);

        $ticket = DB::transaction(function () use ($user, $validated): SupportTicket {
            $ticketNumber = 'TCK-'.date('ymd').'-'.strtoupper(Str::random(5));

            /** @var SupportTicket $t */
            $t = SupportTicket::create([
                'ticket_number' => $ticketNumber,
                'user_id' => $user->id,
                'order_id' => $validated['order_id'] ?? null,
                'department' => $validated['department'],
                'priority' => $validated['priority'],
                'status' => TicketStatus::Open,
                'subject' => $validated['subject'],
                'last_reply_at' => now(),
            ]);

            $t->messages()->create([
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_staff' => false,
                'attachments' => $validated['attachments'] ?? [],
            ]);

            return $t;
        });

        return response()->json([
            'success' => true,
            'message' => __('messages.tickets.created_success'),
            'data' => [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->value,
            ],
        ], 201);
    }

    /**
     * Show ticket details and chat thread.
     */
    public function show(Request $request, string $ticketNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('ticket_number', $ticketNumber)
            ->with(['order', 'messages.user:id,first_name,last_name'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'department' => $ticket->department->value,
                'department_label' => $ticket->department->label(),
                'priority' => $ticket->priority->value,
                'priority_label' => $ticket->priority->label(),
                'priority_color' => $ticket->priority->color(),
                'status' => $ticket->status->value,
                'status_label' => $ticket->status->label(),
                'status_color' => $ticket->status->color(),
                'order_number' => $ticket->order?->order_number,
                'messages' => $ticket->messages->map(fn ($m) => [
                    'id' => $m->id,
                    'message' => $m->message,
                    'is_staff' => $m->is_staff,
                    'author_name' => $m->is_staff ? 'کارشناس پشتیبانی' : ($m->user ? trim(($m->user->first_name ?? '').' '.($m->user->last_name ?? '')) : 'شما'),
                    'attachments' => $m->attachments ?? [],
                    'created_at' => $m->created_at->toIso8601String(),
                    'created_at_jalali' => Jalalian::fromCarbon($m->created_at)->format('Y/m/d H:i'),
                ]),
                'created_at' => $ticket->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::fromCarbon($ticket->created_at)->format('Y/m/d H:i'),
            ],
        ]);
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, string $ticketNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        if ($ticket->status === TicketStatus::Closed) {
            return response()->json([
                'success' => false,
                'message' => __('messages.tickets.already_closed'),
            ], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:3000'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['string'],
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_staff' => false,
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $ticket->update([
            'status' => TicketStatus::Open,
            'last_reply_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.tickets.reply_success'),
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Close a ticket.
     */
    public function close(Request $request, string $ticketNumber): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        $ticket->update([
            'status' => TicketStatus::Closed,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.tickets.closed_success'),
        ]);
    }
}
