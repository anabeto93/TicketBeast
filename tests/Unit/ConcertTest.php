<?php

namespace Tests\Unit;

use App\Models\Concert;
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
    function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets('me@admin.com', 5);//order 5 tickets

        $this->assertEquals('me@admin.com', $order->email);
        $this->assertEquals(5, $order->tickets()->count());
    }
}
