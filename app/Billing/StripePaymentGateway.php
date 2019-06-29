<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Error\InvalidRequest;
use Stripe\Token;

class StripePaymentGateway implements PaymentGateway
{
    private $api_key;

    public function __construct()
    {
        $this->api_key = config('services.stripe.secret');
    }

    public function charge($amount, $token)
    {
        try{
            $charge = Charge::create([
                "amount" => $amount,
                "currency" => "usd",
                "source" => $token, // obtained with Stripe.js
                "description" => "Charge for jenny.rosen@example.com"
            ],['api_key' => $this->api_key]);
        }catch(InvalidRequest $e) {
            throw new PaymentFailedException;
        }
    }

    public function getValidTestToken()
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 6,
                'exp_year' => date('Y') + 1,
                'cvc' => '123'
            ]
        ], ['api_key' => $this->api_key ])->id;
    }

    public function lastCharge()
    {
        return Charge::all(['limit' => 1],
            ['api_key' => $this->api_key ])['data'][0];
    }

    public function newChargesDuring($callback)
    {
        $last_charge = $this->lastCharge();

        $callback();

        return $this->newChargesSince($last_charge)->pluck('amount');
    }

    public function newChargesSince($last_charge = null)
    {
        $new_charges = Charge::all([
            'limit' => 1,
            'ending_before' => $last_charge ? $last_charge->id : null],
            ['api_key' => $this->api_key ])['data'];
        
        return collect($new_charges);
    }
}
