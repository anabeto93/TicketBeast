<?php

namespace Tests\Unit;


use App\Facades\TicketCode;
use App\Models\Ticket;
use App\Repositories\Ticket\TicketCodeGeneratorRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function ticket_codes_are_at_least_six_characters_long()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');


        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) == 6);
    }

    /** @test */
    function ticket_codes_can_only_contain_uppercase_letters()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');

        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertRegExp('/^[A-Z]+$/', $code);
    }

    /** @test */
    function ticket_codes_for_the_same_ticket_ids_are_the_same()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    /** @test */
    function ticket_codes_for_the_different_ticket_ids_are_different()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    /** @test */
    function ticket_codes_generated_with_different_salts_for_same_ticket_are_different()
    {
        $ticketCodeGenerator1 = new TicketCodeGeneratorRepository('testsalt1');
        $ticketCodeGenerator2 = new TicketCodeGeneratorRepository('testsalt2');

        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
    
    /**
     * @test
     */
    function ticket_codes_generated_are_stored_in_db()
    {
        $order = factory(\App\Models\Order::class)->create();
        $ticket = factory(Ticket::class)->create(['code' => null]);

        TicketCode::shouldReceive('generateFor')->once()->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        $this->assertDatabaseHas('tickets', [
           'code' => 'TICKETCODE1'
        ]);
    }
}