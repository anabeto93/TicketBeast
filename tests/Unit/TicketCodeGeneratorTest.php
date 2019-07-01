<?php

namespace Tests\Unit;


use App\Models\Ticket;
use App\Repositories\Ticket\TicketCodeGeneratorRepository;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ticket_codes_are_at_least_six_characters_long()
    {
        $this->disableExceptionHandling();

        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');


        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) == 6);
    }

    /** @test */
    public function ticket_codes_can_only_contain_uppercase_letters()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');

        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertRegExp('/^[A-Z]+$/', $code);
    }

    /** @test */
    public function ticket_codes_for_the_same_ticket_ids_are_the_same()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    /** @test */
    public function ticket_codes_for_the_diffent_ticket_ids_are_the_different()
    {
        $ticketCodeGenerator = new TicketCodeGeneratorRepository('testsalt');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    /** @test */
    public function ticket_codes_generated_with_different_salts_are_different()
    {
        $ticketCodeGenerator1 = new TicketCodeGeneratorRepository('testsalt1');
        $ticketCodeGenerator2 = new TicketCodeGeneratorRepository('testsalt2');

        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}