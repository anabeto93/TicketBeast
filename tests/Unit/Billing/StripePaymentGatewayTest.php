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
    private $last_charge;

    public function setUp(): void
    {
        parent::setUp();
        $this->last_charge = $this->lastCharge();
    }

    private function lastCharge()
    {
        return \Stripe\Charge::all(['limit' => 1],
            ['api_key' => config('services.stripe.secret')])['data'][0];
    }

    private function validToken()
    {
        return \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 6,
                'exp_year' => date('Y') + 1,
                'cvc' => '123'
            ]
        ], ['api_key' => config('services.stripe.secret')])->id;
    }

    private function newCharges()
    {
        return \Stripe\Charge::all([
            'limit' => 1,
            'ending_before' => $this->last_charge->id],
            ['api_key' => config('services.stripe.secret')])['data'];
    }

    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
    {
        $paymentGateway = new StripePaymentGateway;

        $paymentGateway->charge(2500, $this->validToken());

        $this->assertCount(1, $this->newCharges());

        $this->assertEquals(2500, $this->lastCharge()->amount);
    }
}