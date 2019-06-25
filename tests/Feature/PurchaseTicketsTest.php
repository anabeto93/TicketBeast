<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function customer_can_purchase_concert_tickets() 
    {
        $paymentGateway = new FakePaymentGateway();

        $this->app->instance(PaymentGateway::class, $paymentGateway);
        // Arrange
        $concert = factory(Concert::class)->create([
            'ticket_price' => 1599
        ]);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'test@admin.com',
            'ticket_quantity' => 2,
            'payment_token' => $paymentGateway->getValidTestToken(),
        ]);

        //Assert that ticket has been created
        $response->assertStatus(201);

        //Ensure customer was charged
        $this->assertEquals(3198, $paymentGateway->totalCharges());

        //Ensure an order exists for the customer
        $order = $concert->orders()->where('email', 'test@admin.com')->first();

        $this->assertNotNull($order);

        $this->assertEquals(2, $order->tickets()->count());
    }

    /**
     * @test
     */
    function email_is_required_to_purchase_tickets()
    {
        $paymentGateway = new FakePaymentGateway();

        $this->app->instance(PaymentGateway::class, $paymentGateway);

        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 2,
            'payment_token' => $paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(422);
    }
}
