<?php

namespace App\Billing;

use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
    public function charge($amount, $token)
    {

        $charge = Charge::create([
            "amount" => $amount,
            "currency" => "usd",
            "source" => $token, // obtained with Stripe.js
            "description" => "Charge for jenny.rosen@example.com"
        ],['api_key' => config('services.stripe.secret')]);
    }
}
