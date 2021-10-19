<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Contractor;
use App\Models\Building;
use App\Models\Work;
use Faker\Generator as Faker;

$factory->define(Contractor::class, function (Faker $faker) {
    return [
        'title' => $faker->company,
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->phoneNumber,
        'sum' => $faker->numberBetween(0, 17500000),
        'building_id' => Building::all('id')->random(),
        'work_id' => Work::all('id')->random(),
    ];
});
