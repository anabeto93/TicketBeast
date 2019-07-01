<?php

namespace App\Repositories\Ticket;

interface TicketCodeGeneratorContract
{
    public function generateFor(\App\Models\Ticket $ticket);
}