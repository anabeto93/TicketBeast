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

        //$this->withoutMiddleware([VerifyCsrfToken::class]);

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
    function customer_can_purchase_published_concert_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 1599
        ]);

        $concert->addTickets(5); //more than the 2 that will be bought

        $response = $this->orderTickets($concert, [
            'email' => 'test@admin.com',
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        //Assert that ticket has been created
        $response->assertStatus(201);

        //assert against the expected response if it has been created
        $response->assertJson( [
                "email" => "test@admin.com",
                "ticket_quantity" => 2,
                "amount" => 3198,
        ]);

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
    function cannot_purchase_unpublished_concert_tickets()
    {
        $concert = factory(Concert::class)->state('unpublished')->create([
            'ticket_price' => 670
        ]);

        $concert->addTickets(30);//10 more than necessary

        $response = $this->orderTickets($concert, [
            'email' => 'cheap@girls.com',
            'ticket_quantity' => 20,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(404);

        $order = $concert->orders()->where('email', 'cheap@girls.com')->first();

        $this->assertNull($order);

        //assert no tickets sold
        $this->assertEquals(0, $concert->orders()->count());

        //Ensure customer is not charged for those tickets
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    function order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $concert->addTickets(15);

        $response = $this->orderTickets($concert, [
            'email' => 'fake@user.com',
            'ticket_quantity' => 4,
            'payment_token' => 'fake_invalid_token_hahaha',
        ]);

        $response->assertStatus(422);

        $order = $concert->orders()->where('email', 'fake@user.com')->first();

        $this->assertNull($order);
    }

    /**
     * @test
     */
    function email_is_required_to_purchase_tickets()
    {

        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'email');
    }

    /**
     * @test
     */
    function valid_email_is_required_to_purchase_tickets()
    {

        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'some_email',
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
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'ticket@quantity.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    function ticket_quantity_should_at_least_be_one_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'ticket@quantity.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'ticket@quantity.com',
            'ticket_quantity' => 2,
        ]);

        $this->assertValidationError($response, 'payment_token');
    }

    /**
     * @test
     */
    function cannot_buy_more_tickets_than_is_available()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $concert->addTickets(58);

        $response = $this->orderTickets($concert, [
            'email' => 'ticket@quantity.com',
            'ticket_quantity' => 60,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(422); //cannot buy more than is available

        $order = $concert->orders()->where('email','ticket@quantity.com')->first();
        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(58, $concert->remainingTickets());
    }

    /**
     * @test
     */
    function cannot_purchase_tickets_another_has_reserved()
    {
        $this->disableExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 1500
        ]);
        $concert->addTickets(5);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use($concert) {

            $requestA = $this->app['request'];

            $responseB = $this->orderTickets($concert, [
                'email' => 'personB@admin.com',
                'ticket_quantity' => 4,
                'payment_token' => $paymentGateway->getValidTestToken(),
            ]);

            //restore requestA
            $this->app['request'] = $requestA;

            $responseB->assertStatus(422); //cannot buy more than is available

            $order = $concert->orders()->where('email','personB@admin.com')->first();
            $this->assertNull($order);
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $responseA = $this->orderTickets($concert, [
            'email' => 'personA@admin.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        //Assert that ticket has been created
        $responseA->assertStatus(201);

        //dd($concert->orders()->first()->toArray());

        //assert against the expected response if it has been created
        $responseA->assertJson( [
            "email" => "personA@admin.com",
            "ticket_quantity" => 3,
            "amount" => 4500,
        ]);

        //Ensure customer was charged
        $this->assertEquals(4500, $this->paymentGateway->totalCharges());

        //Ensure an order exists for the customer
        $order = $concert->orders()->where('email', 'personA@admin.com')->first();

        $this->assertNotNull($order);

        $this->assertEquals(3, $order->tickets()->count());
    }
}
