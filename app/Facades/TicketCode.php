<?php

namespace App\Facades;

use App\Repositories\Ticket\TicketCodeGeneratorContract;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;


class TicketCode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TicketCodeGeneratorContract::class;
    }

    protected static function getMockableClass()
    {
        Log::info('Called');
        return static::getFacadeAccessor();
    }
}