<?php

namespace App\Billing;

interface PaymentGateway 
{
    /**
     * @param $amount
     * @param $token
     * @return \App\Billing\Charge
     */
    public function charge($amount, $token);

    public function getValidTestToken();

    public function newChargesDuring($callback);

    public function lastCharge();
}