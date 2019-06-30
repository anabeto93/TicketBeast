<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    public function show($confirmation_number)
    {
        $order = Order::findByConfirmationNumber($confirmation_number);
        Log::info('The order that was obtained',['order' => $order]);
        return view('orders.show')->withOrder($order);
    }
}
