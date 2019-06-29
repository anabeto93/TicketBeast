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
    
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
    }

    /**
     * @test  
     */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(1300, $paymentGateway->getValidTestToken());

        $this->assertEquals(1300, $paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    function charges_with_invalid_payment_token_fails()
    {
        try{
            $paymentGateway = $this->getPaymentGateway();

            $paymentGateway->charge(2500, 'hahaha-faked-token');
        }catch(\App\Billing\PaymentFailedException $exception) {

            $this->assertTrue(true);

            return;
        }

        $this->fail();
    }

    /**
     * @test
     */
    function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = $this->getPaymentGateway();

        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $timesCallbackRan++;
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(1300, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(3800, $paymentGateway->totalCharges());
    }
}