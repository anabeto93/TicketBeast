<?php

namespace App\Models;

use App\Repositories\Order\OrderConfirmationNumberGeneratorContract as OrderConfirmationNumberGenerator;
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

    public static function forTickets($tickets, $email, $amount)
    {
        $order = self::create([
            'confirmation_number' => app(OrderConfirmationNumberGenerator::class)->generate(),
            'email' => $email,
            'amount' => $amount,
            //ticket prices can vary, add them individually if no amount supplied
        ]);

        foreach($tickets as $ticket) {
            $order->tickets()->save($ticket);//associate the two
        }

        return $order;
    }

    public static function findByConfirmationNumber($confirmation_number)
    {
        return self::where('confirmation_number', $confirmation_number)->firstOrFail();
    }
}
