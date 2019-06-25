<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test  
     */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(1300, $paymentGateway->getValidTestToken());

        $this->assertEquals(1300, $paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    function charges_with_invalid_payment_token_fails()
    {
        $this->disableExceptionHandling();

        try{
            $paymentGateway = new FakePaymentGateway;

            $paymentGateway->charge(2500, 'hahaha-faked-token');
        }catch(\App\Billing\PaymentFailedException $exception) {

            $this->assertTrue(true);

            return;
        }

        $this->fail();
    }
}