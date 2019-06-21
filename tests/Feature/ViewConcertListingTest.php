<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Concert;
use Carbon\Carbon;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function user_can_view_a_concert_listing()
    {
        //Arrange
        //Given that we have a concert listing
        $concert = Concert::create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('June 30, 2019 9:00am'),
            'ticket_price' => 3250,
            'venue' => 'Devcongress',
            'venue_address' => 'Accra, Ghana',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'zip' => '00233',
            'additional_information' => 'For tickets, call (233203833803).'
        ]);

        //Act
        //When we view or access the concert listing
        $response = $this->get('/concerts/'.$concert->id);

        //Assert
        //Then, we should see the concert details
        $response->assertStatus(200);
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('June 30, 2019');
        $response->assertSee('9:00am');
        $response->assertSee('Devcongress');
        $response->assertSee('32.50');
        $response->assertSee('Devcongress');
        $response->assertSee('Accra, Ghana');
        $response->assertSee('Accra');
        $response->assertSee('Greater Accra');
        $response->assertSee('00233');
        $response->assertSee('For tickets, call (233203833803).');

        $this->assertDatabaseHas('concerts', ['title' => 'The Red Chord']);
    }
}
