<?php

namespace App\Models;

use App\Facades\OrderConfirmationNumberGenerator;
use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
    protected $guarded = [];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticket_quantity()
    {
        return $this->tickets()->count();
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticket_quantity(),
            'amount' => $this->amount,
            'confirmation_number' => $this->confirmation_number,
        ];
    }

    public static function forTickets($tickets, $email, \App\Billing\Charge $charge)
    {
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumberGenerator::generate(),
            'email' => $email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour(),
        ]);

        $tickets->each->claimFor($order);

        return $order;
    }

    public static function findByConfirmationNumber($confirmation_number)
    {
        return self::where('confirmation_number', $confirmation_number)->firstOrFail();
    }
}
