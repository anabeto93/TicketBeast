<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Reservation;
use App\Models\Ticket;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    function calculating_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 2000],
            (object) ['price' => 2000],
            (object) ['price' => 5000],
            (object) ['price' => 1500],
        ]);

        $reservation = new Reservation($tickets,'just@me.com');

        $this->assertEquals(10500, $reservation->totalCost());
    }

    /**
     * @test
     */
    function reserved_tickets_are_released_when_reservation_is_cancelled()
    {
        $tickets =[];

        foreach(range(0, 2) as $i) {
            $tickets[$i] = Mockery::spy(Ticket::class);
        }

        $tickets = collect($tickets);

        $reservation = new Reservation($tickets,'come@on.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release')->once();
        }
    }

    /**
     * @test
     */
    function can_get_reservation_tickets()
    {
        $tickets = collect([
            (object) ['price' => 2000],
            (object) ['price' => 2000],
            (object) ['price' => 5000],
            (object) ['price' => 1500],
        ]);

        $reservation = new Reservation($tickets,'hmm@tired.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

    /**
     * @test
     */
    function can_get_customer_email_address_from_reservation()
    {
        $tickets = collect([
            (object) ['price' => 2000],
            (object) ['price' => 2000],
            (object) ['price' => 5000],
            (object) ['price' => 1500],
        ]);

        $reservation = new Reservation($tickets,'hmm@tired.com');

        $this->assertEquals($tickets, $reservation->tickets());
        $this->assertEquals('hmm@tired.com', $reservation->email());
    }

    /**
     * @test
     */
    function completing_a_reservation()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1000]);
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
        $reservation = new Reservation($tickets, 'getting@haha.com');

        $order = $reservation->complete();

        $this->assertEquals('getting@haha.com', $order->email);
        $this->assertEquals(3, $order->ticket_quantity());
        $this->assertEquals(3000, $order->amount);
    }
}