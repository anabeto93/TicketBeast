<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function convert_order_to_array_works()
    {
        $this->disableExceptionHandling();

        $concert = factory(Concert::class)->create([
            'ticket_price' => 100
        ]);

        $concert->addTickets(10);

        $order = $concert->orderTickets('array@order.com', 6);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'array@order.com',
            'ticket_quantity' => 6,
            'amount' => 600 //100 * 6
        ], $result);
    }

    /**
     * @test
     */
    function tickets_are_released_when_an_order_is_cancelled()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(15);
        $order = $concert->orderTickets('tu3@order.com', 6);
        $this->assertEquals(9, $concert->remainingTickets());//9+6==15

        $order->cancel();

        $this->assertEquals(15, $concert->remainingTickets());
        $this->assertNull(Order::find($order->id));
    }
}