<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Ticket;
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
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);//just one

        $order = $concert->orderTickets('release@ticket.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
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