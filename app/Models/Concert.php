<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute() 
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInFloatAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderTickets($email, $ticket_quantity)
    {
        $order = $this->orders()->create(['email' => $email]);

        foreach(range(1, $ticket_quantity) as $i) {
            $order->tickets()->create([]);
        }

        return $order;
    }
}
