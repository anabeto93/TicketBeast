<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StripePaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
    {
        $paymentGateway = new StripPaymentGateway;

        $token = \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 6,
                'exp_year' => date('Y') + 1,
                'cvc' => '123'
            ]
        ], ['api_key' => config('services.stripe.secret')])->id;

        $paymentGateway->charge(2500, $token);

        $ch = \Stripe\Charge::retrieve($token, ['api_key' => config('services.stripe.secret')]);
        
    }
}