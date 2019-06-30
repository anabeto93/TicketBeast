<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Order;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'amount' => rand(1000,9999),
        'email' => $faker->companyEmail
    ];
});
