<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Concert;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(100),
        'subtitle' => $faker->realText(),
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => rand(100, 9999),
        'venue' => $faker->name,
        'venue_address' => $faker->address,
        'city' => $faker->city,
        'state' => $faker->streetAddress,
        'zip' => rand(1000,9999),
        'additional_information' => $faker->realText()
    ];
});

$factory->state(Concert::class, 'published', function($faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
    ];
});

$factory->state(Concert::class, 'unpublished', function($faker) {
    return [
        'published_at' => null,
    ];
});
