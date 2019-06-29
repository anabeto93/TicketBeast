<?php

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConcertsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'Card Systems - Payments Industry',
            'subtitle' => 'with Emmanuel Osei - Akoto',
            'date' => Carbon::parse('June 30, 2019 9:00am'),
            'ticket_price' => 3250,
            'venue' => 'Payswitch LLC',
            'venue_address' => 'Justice Azu Crabbe Street',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'zip' => '00233',
            'additional_information' => 'For tickets, call (+233203833803).',
        ]);

        $concert->addTickets(10);
    }
}
