<?php

namespace Tests\Unit;

use App\Models\Reservation;
use App\Models\Ticket;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTestTest extends TestCase
{
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

        $reservation = new Reservation($tickets);

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

        $reservation = new Reservation($tickets);

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release')->once();
        }
    }
}