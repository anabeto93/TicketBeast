<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Ticket;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function a_ticket_can_be_released()
    {
        $ticket = factory(Ticket::class)->state('reserved')->create();

        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserverd_at);
    }

    /**
     * @test
     */
    function can_reserve_a_ticket()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }
}