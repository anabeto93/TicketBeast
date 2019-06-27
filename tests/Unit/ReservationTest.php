<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Reservation;
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
}