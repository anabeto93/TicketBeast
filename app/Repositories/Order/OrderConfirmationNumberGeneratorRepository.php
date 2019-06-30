<?php

namespace App\Repositories\Order;

class OrderConfirmationNumberGeneratorRepository implements OrderConfirmationNumberGeneratorContract
{
    /**
     * Returns a unique 24 character string without ambiguous characters
     * @return string
     */
    public function generate()
    {
        $pool = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}