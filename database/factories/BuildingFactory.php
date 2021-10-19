<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Building;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Building::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(3),
        'address' => $faker->address,
        'floors' => $faker->numberBetween(1, 19),
        'responsible' => $faker->name,
        'area' => $faker->randomFloat(2, 10, 160),
        'closed' => $faker->boolean,
        'user_created' => 1,
    ];
});
