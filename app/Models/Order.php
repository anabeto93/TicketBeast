<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        $this->delete();//delete it after releasing the tickets
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
//            'amount' => $this->ticket_quantity() * $this->concert->ticket_price,
        ];
    }

    public static function forTickets($tickets, $email)
    {
        $order = self::create([
            'email' => $email,
            'amount' => $tickets->sum('price'),//ticket prices can vary, add individually
        ]);

        foreach($tickets as $ticket) {
            $order->tickets()->save($ticket);//associate the two
        }

        return $order;
    }
}
