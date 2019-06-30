<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function user_can_view_order_confirmation()
    {
        $concert = factory(Concert::class)->create();
        $order = factory(Order::class)->create([
            'confirmation_number' => 'SOMEORDERCONFIRMATION54321',
            'amount' => 3500,
            'card_last_four' => '1881'
        ]);

        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE123'
        ]);

        $ticketB = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE456'
        ]);


        $response = $this->get('/orders/'.$order->confirmation_number);
        $response->assertStatus(200);
        $response->assertViewHas('order', $order);

        $response->assertSee('SOMEORDERCONFIRMATION54321');
        $response->assertSee('Order Total: $35.00');
        $response->assertSee('*** **** **** 1881');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');
        $this->assertEquals(2, $order->tickets()->count());
        $response->assertSee($ticket->order->email);
    }
}
