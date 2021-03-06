<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Ticket;
use Faker\Generator as Faker;

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function() {
            return factory(\App\Models\Concert::class)->create()->id;
        }
    ];
});

$factory->state(Ticket::class,'reserved', function($faker) {
    return [
        'reserved_at' => \Carbon\Carbon::parse('-1 day'),
    ];
});
