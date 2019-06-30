<?php

namespace Tests\Unit\Billing;

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
    use RefreshDatabase, PaymentGatewayContractTest;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway();
    }
}