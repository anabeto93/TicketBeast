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
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2019-06-21 1:03am'),
        ]);

        //retrieve the formatted date
        $date = $concert->formatted_date;

        //Verify date is formatted as expected
        $this->assertEquals($date, 'June 21, 2019');
    }
}
