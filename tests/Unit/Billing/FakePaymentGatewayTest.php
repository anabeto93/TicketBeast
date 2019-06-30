<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentTest extends TestCase
{
    use RefreshDatabase, PaymentGatewayContractTest;
    
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
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