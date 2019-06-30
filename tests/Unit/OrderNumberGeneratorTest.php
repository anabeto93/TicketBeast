<?php

namespace Tests\Unit;

use App\Repositories\Order\OrderConfirmationNumberGeneratorRepository as OrderConfirmationNumberGenerator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class OrderNumberGeneratorTest extends TestCase
{
    //RULES:
    /*
     * Can only contain uppercase letters and numbers
     * Cannot contain ambiguous characters like 0,O, 1 and I
     * Must be 24 characters long
     * all order confirmation numbers must be unique
     * Char List ABCDEFGHJKLMNOPQRSTUVWXYZ
     * Num List 23456789
     */

    /**
     * @test
     */
    function must_be_24_characters_long()
    {
        $generator = new OrderConfirmationNumberGenerator;

        $number = $generator->generate();

        $this->assertEquals(24, strlen($number));
    }

    /**
     * @test
     */
    function can_only_contain_uppercase_letters_and_numbers()
    {
        $generator = new OrderConfirmationNumberGenerator;

        $number = $generator->generate();

        $this->assertRegExp('/^[A-Z0-9]+$/', $number);
    }

    /**
     * @test
     */
    function cannot_contain_ambiguous_characters_and_numbers()
    {
        $generator = new OrderConfirmationNumberGenerator;

        $number = $generator->generate();

        $this->assertFalse(strpos($number, '1'));
        $this->assertFalse(strpos($number, 'I'));
        $this->assertFalse(strpos($number, 'O'));
        $this->assertFalse(strpos($number, '0'));
    }

    /**
     * @test
     */
    function must_contain_at_least_one_number()
    {
        $generator = new OrderConfirmationNumberGenerator;

        $number = $generator->generate();

        $this->assertRegExp('/\d{1,24}/', $number);
    }

    /**
     * @test
     */
    function must_be_a_unique_confirmation_number()
    {
        $generator = new OrderConfirmationNumberGenerator;

        $numbers = array_map(function() use($generator) {
            return $generator->generate();
        }, range(1, 100));

        $duplicates = array_unique($numbers);

        $this->assertCount(100, $duplicates);
    }
}