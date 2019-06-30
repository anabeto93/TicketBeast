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
            'confirmation_number' => 'SOMEORDERCONFIRMATION54321'
        ]);

        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);


        $response = $this->get('/orders/'.$order->confirmation_number);
        $response->assertStatus(200);
        $response->assertViewHas('order', $order);
    }
}
