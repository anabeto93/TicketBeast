<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;

trait PaymentGatewayContractTest
{
    /**
     * @return PaymentGateway
     */
    abstract protected function getPaymentGateway();

    /**
     * @test
     */
    function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(1500, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 3000], $newCharges->all());
    }

    /**
     * @test
     */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $charge = $paymentGateway->lastCharge();

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    }

    /**
     * @test
     */
    function charges_with_invalid_payment_token_fails()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
            try{
                $paymentGateway->charge(2500, 'hahaha-faked-token');
            }catch(PaymentFailedException $exception) {

                return;
            }

            $this->fail('Charging with an invalid stripe token not throwing the PaymentFailedException');
        });

        $this->assertCount(0, $newCharges);
    }
}