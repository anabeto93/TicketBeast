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
    function tickets_are_released_when_an_order_is_cancelled()
    {
        $this->disableExceptionHandling();

        $concert = factory(Concert::class)->create();
        $concert->addTickets(15);
        $order = $concert->orderTickets('tu3@order.com', 6);
        $this->assertEquals(9, $concert->remainingTickets());//9+6==15

        $order->cancel();

        $this->assertEquals(15, $concert->remainingTickets());
        $this->assertNull(Order::find($order->id));
    }
}