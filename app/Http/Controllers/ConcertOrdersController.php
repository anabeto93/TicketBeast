<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
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
        $concert = Concert::published()->findOrFail($id);

        $this->validate(request(), [
            'email' => 'bail|required|email',
            'ticket_quantity' => 'bail|required|integer|min:1',
            'payment_token' => 'bail|required'
        ]);

        try{

            //Creating the order
            $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

            //Charging the customer
            $this->paymentGateway
                ->charge(request('ticket_quantity') * $concert->ticket_price,
                    request('payment_token'));

            return response()->json([], 201);
        }catch(\App\Billing\PaymentFailedException $e) {
            Log::info('The order to cancel on failed payment');
            foreach($order->tickets as $ticket) {
                Log::debug($ticket);
            }
            $order->cancel();//cancel the order if payment couldn't be completed successfully

            return response()->json([], 422);
        }catch(NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
