<?php

namespace App\Facades;

use App\Repositories\Order\OrderConfirmationNumberGeneratorContract;
use Illuminate\Support\Facades\Facade;

class OrderConfirmationNumberGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return  OrderConfirmationNumberGeneratorContract::class;
    }
}