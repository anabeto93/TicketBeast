<?php

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
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
    use RefreshDatabase, PaymentGatewayContractTest;

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway();
    }
}