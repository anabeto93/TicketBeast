<?php

namespace App\Models;

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
     * @return mixed
     */
    public function complete($paymentGateway, $payment_token)
    {
        $paymentGateway->charge($this->totalCost(), $payment_token);

        return Order::forTickets($this->tickets, $this->email, $this->totalCost());
    }
}