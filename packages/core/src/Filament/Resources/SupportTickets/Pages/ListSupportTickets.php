<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\SupportTickets\Pages;

use Reyhan\Core\Filament\Resources\SupportTickets\SupportTicketResource;
use Filament\Resources\Pages\ListRecords;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;
}
