<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $gateway)
    {
        $this->paymentGateway = $gateway;
        //Log::info('Constructor has been called to do the binding');
    }

    public function store($id) 
    {
        Log::info('Request to create an order and reserve tickets ');
        Log::debug(request()->all());
        $concert = Concert::published()->findOrFail($id);

        $this->validate(request(), [
            'email' => 'bail|required|email',
            'ticket_quantity' => 'bail|required|integer|min:1',
            'payment_token' => 'bail|required'
        ]);

        try{
            $reservation = $concert->reserveTickets(request('ticket_quantity'),
                request('email'));

            $order = $reservation->complete($this->paymentGateway, request('payment_token'));

            return response()->json($order->toArray(), 201);
        }catch(PaymentFailedException $e) {
            $reservation->cancel();

            return response()->json([], 422);
        }catch(NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
