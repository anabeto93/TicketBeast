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
                "description" => "Charge for richard@humvite.com"
            ],['api_key' => $this->api_key]);

            return new \App\Billing\Charge([
                'amount' => $charge['amount'],
                'card_last_four' => $charge['payment_method_details']['card']['last4'],
            ]);
        }catch(InvalidRequest $e) {
            throw new PaymentFailedException;
        }
    }

    public function getValidTestToken($card_number = '4242424242424242')
    {
        return Token::create([
            'card' => [
                'number' => $card_number,
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

        $callback($this);

        return $this->newChargesSince($last_charge)->map(
            function($charge) {
                return new \App\Billing\Charge([
                    'amount' => $charge['amount'],
                    'card_last_four' => $charge['payment_method_details']['card']['last4'],
                ]);
            }
        );
    }

    public function newChargesSince($last_charge = null)
    {
        $new_charges = Charge::all([
            'ending_before' => $last_charge ? $last_charge->id : null],
            ['api_key' => $this->api_key ])['data'];

        return collect($new_charges);
    }
}
