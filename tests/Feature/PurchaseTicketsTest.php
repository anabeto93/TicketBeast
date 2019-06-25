<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    private $paymentGateway;

    protected function setUp() : void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();

        $this->withoutMiddleware([VerifyCsrfToken::class]);

        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($response, $field_name)
    {
        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message', 'errors' => [$field_name => []]
        ]);
    }

    /**
     * @test
     */
    function customer_can_purchase_concert_tickets() 
    {
        // Arrange
        $concert = factory(Concert::class)->create([
            'ticket_price' => 1599
        ]);

        $response = $this->orderTickets($concert, [
            'email' => 'test@admin.com',
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        //Assert that ticket has been created
        $response->assertStatus(201);

        //Ensure customer was charged
        $this->assertEquals(3198, $this->paymentGateway->totalCharges());

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

        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'email');
    }

    /**
     * @test
     */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email' => 'ticket@quantity.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email' => 'ticket@quantity.com',
            'ticket_quantity' => 2,
        ]);

        $this->assertValidationError($response, 'payment_token');
    }
}
