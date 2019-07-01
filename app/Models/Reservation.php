<?php

namespace App\Models;

use App\Billing\PaymentGateway;

class Reservation
{
    private $tickets;
    private $email;

    public function __construct($tickets, $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function cancel()
    {
        foreach($this->tickets as $ticket) {
            $ticket->release();
        }
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }

    /**
     * Complete a reservation by charging and then creating an order
     * @param PaymentGateway $paymentGateway
     * @param string $payment_token
     * @return Order
     */
    public function complete(PaymentGateway $paymentGateway, $payment_token)
    {
        $charge = $paymentGateway->charge($this->totalCost(), $payment_token);

        return Order::forTickets($this->tickets, $this->email, $charge);
    }
}