<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGateway
{
    private $api_key;

    public function __construct($api_key)
    {
        $this->api_key = $api_key === null ? config('services.stripe.secret') : $api_key;
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
}
