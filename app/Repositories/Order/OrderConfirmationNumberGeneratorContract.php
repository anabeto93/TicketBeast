<?php

namespace App\Repositories\Order;

interface OrderConfirmationNumberGeneratorContract
{
    /**
     * Returns a unique 24 character string without ambiguous characters
     * @return string
     */
    public function generate();
}