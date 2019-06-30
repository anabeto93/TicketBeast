<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
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
        $order = factory(Order::class)->create([
            'email' => 'array@order.com',
            'amount' => 600,
            'confirmation_number' => 'BMNJXHVRAS5EGXJPJMZ8XW88',
        ]);

        $tickets = $order->tickets()->saveMany(
            factory(Ticket::class, 6)->create()
        );

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'BMNJXHVRAS5EGXJPJMZ8XW88',
            'email' => 'array@order.com',
            'ticket_quantity' => 6,
            'amount' => 600 //100 * 6
        ], $result);
    }

    /**
     * @test
     */
    function can_create_order_given_tickets_email_and_amount()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(15);

        $this->assertEquals(15, $concert->remainingTickets());

        $order = Order::forTickets($concert->findTickets(10),
            'order@tickets.com', 22500);

        $this->assertEquals('order@tickets.com', $order->email);

        $this->assertEquals(10, $order->ticket_quantity());
        $this->assertEquals(22500, $order->amount);
        $this->assertEquals(5, $concert->remainingTickets());
    }

    /**
     * @test
     */
    function can_retrieve_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'SOMEORDERINFORMATION'
        ]);

        $founder_order = Order::findByConfirmationNumber('SOMEORDERINFORMATION');

        $this->assertEquals($order->id, $founder_order->id);
    }

    /**
     * @test
     */
    function retrieving_non_existent_order_by_confirmation_number_throws_an_exception()
    {
        try{
            $founder_order = Order::findByConfirmationNumber('MWAHAHAHAHAA');
        }catch(ModelNotFoundException $e) {
            $this->assertEquals(1,1);
            return;
        }

        $this->fail('No matching order was found but an exception was not thrown');
    }
}