<?php

use App\Models\User;
use App\Models\Priority;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->Firstname,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'password' => 'secret'
    ];
});

$factory->defineAs(User::class,'admin', function (Faker\Generator $faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, ['is_admin' => true]);
});

$factory->define(Priority::class, function (Faker\Generator $faker){
    return [
        'name' => $faker->word
    ];
});

