<?php

namespace Tests\Feature;

use App\Billing\StripePaymentGateway;
use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class StripePaymentGatewayTest
 * @package Tests\Feature
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway();
    }

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
            $paymentGateway->charge(700, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(800, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([700, 800], $newCharges->all());
    }

    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
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
    function charges_with_invalid_stripe_token_fails()
    {
        try{
            $paymentGateway = $this->getPaymentGateway();

            $paymentGateway->charge(2500, 'hahaha-faked-token');
        }catch(\App\Billing\PaymentFailedException $exception) {

            $this->assertTrue(true);

            return;
        }

        $this->fail('Charging with an invalid stripe token not throwing the PaymentFailedException');
    }
}