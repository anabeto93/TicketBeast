<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConcertTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function can_get_formatted_date()
    {
        //Create a concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2019-06-21 1:03am'),
        ]);

        //retrieve the formatted date
        $date = $concert->formatted_date;

        //Verify date is formatted as expected
        $this->assertEquals($date, 'June 21, 2019');
    }

    /**
     * @test
     */
    function can_get_formatted_start_time() 
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2019-06-21 10:00pm'),
        ]);

        $this->assertEquals('10:00pm', $concert->formatted_start_time);
    }

    /**
     * @test
     */
    function can_get_ticket_price_in_float()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 7850
        ]);

        $this->assertEquals(78.50, $concert->ticket_price_in_float);
    }

    /**
     * @test
     */
    function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = factory(Concert::class)->create([
            'published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /**
     * @test
     */
    function can_add_tickets_to_concert()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(27);

        $this->assertEquals(27, $concert->remainingTickets());
    }

    /**
     * @test
     */
    function tickets_remaining_does_not_include_those_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create();

        $concert->tickets()->saveMany(factory(Ticket::class, 5)->create([
            'order_id' => 1
        ]));

        $concert->tickets()->saveMany(factory(Ticket::class, 12)->create([
            'order_id' => null
        ]));

        $this->assertEquals(12, $concert->remainingTickets());
    }

    /**
     * @test
     */
    function cannot_order_tickets_which_are_already_purchased()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(7);

        //first person to purchase tickets
        $order = factory(Order::class)->create([
            'email' => 'first@mate.com'
        ]);

        $this->assertEquals(7, $concert->remainingTickets());

        $order->tickets()->saveMany($concert->tickets->take(5));//remaining two
        $order->tickets()->update(['reserved_at' => Carbon::now()]);//truly reserved

        try{
            $concert->reserveTickets(5, 'second@person.com');//1 more than available
        }catch (NotEnoughTicketsException $e) {
            $secondOrder = $concert->orders()->where('email','second@person.com')->first();
            $firstOrder = $concert->orders()->where('email','first@mate.com')->first();

            $this->assertNull($secondOrder);

            $this->assertEquals(2, $concert->remainingTickets());

            $this->assertNotNull($firstOrder);

            return;
        }

        $this->fail('Order succeeded even when there were not enough tickets remaining');
    }

    /**
     * @test
     */
    function can_find_tickets()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(7);

        $tickets = $concert->findTickets(4);//anything below or equal to the added quantity

        $this->assertEquals($tickets->count(), 4);
    }

    /**
     * @test
     */
    function can_reserve_available_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(5);

        $this->assertEquals(5, $concert->remainingTickets());

        $reservation = $concert->reserveTickets(2,'vip@me.com');
        $this->assertCount(2, $reservation->tickets());

        $this->assertEquals(3, $concert->remainingTickets());

        $this->assertEquals('vip@me.com', $reservation->email());
    }

    /**
     * @test
     */
    function a_reservation_is_returned_when_concert_tickets_are_reserved()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(2);

        $this->assertEquals(2, $concert->remainingTickets());

        $reservation = $concert->reserveTickets(1,'you@too.com');

        $this->assertTrue($reservation instanceof Reservation);
        $this->assertEquals(1, $concert->remainingTickets());
    }

    /**
     * @test
     */
    function cannot_reserve_already_purchased_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(5);

        $order = factory(Order::class)->create([
            'email' => 'rich@main.com'
        ]);

        $this->assertEquals(5, $concert->remainingTickets());

        $order->tickets()->saveMany($concert->tickets->take(3));//remaining two
        $order->tickets()->update(['reserved_at' => Carbon::now()]);

        try {
            $concert->reserveTickets(4,'greedy@main.com');//meanwhile only 2 left
        }catch (NotEnoughTicketsException $e) {
            $this->assertEquals(2, $concert->remainingTickets());

            return;
        }

        $this->fail('Was able to reserve tickets even though already sold.');
    }

    /**
     * @test
     */
    function cannot_reserve_already_reserved_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(3);

        $concert->reserveTickets(2,'someone@my.business');

        try {
            $concert->reserveTickets(3,'someone@my.business');//meanwhile only 2 left
        }catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->remainingTickets());

            return;
        }

        $this->fail('Was able to reserve tickets even though already reserved.');
    }

    /**
     * @test
     */
    function can_check_concert_has_order_for_customer_given_email()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(2);

        $order = factory(Order::class)->create([
            'email' => 'has@order.com'
        ]);

        $this->assertEquals(2, $concert->remainingTickets());

        $order->tickets()->saveMany($concert->tickets->take(1));//remaining one
        $order->tickets()->update(['reserved_at' => Carbon::now()]);

        $this->assertTrue($concert->hasOrderFor('has@order.com'));
    }
}
