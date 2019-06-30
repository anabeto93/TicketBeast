<?php

namespace App\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\Charge;


class FakePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = '5555555555551881';
    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function getValidTestToken($card_number = self::TEST_CARD_NUMBER)
    {
        $temp = 'fake-tok_'.str_random(24);
        $this->tokens[$temp] = $card_number;
        return $temp;
    }

    public function charge($amount, $token)
    {
        if($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;//prevent it from running again
            $callback($this);
        }
        if(! $this->tokens->has($token)) {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
        ]);
    }

    public function lastCharge()
    {
        return $this->charges->last();
    }

    public function newChargesDuring($callback)
    {
        $charges_from = $this->charges->count();

        $callback($this);

        return $this->charges->slice($charges_from)->reverse()->values();
    }

    public function totalCharges() 
    {
        return $this->charges->map->amount()->sum();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}