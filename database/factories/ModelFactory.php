<?php

use App\Models\User;
use App\Models\Traveler;
use App\Models\Place;
use App\Models\Travel;
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

$factory->define(Traveler::class, function(Faker\Generator $faker){
    return [
        'cedula' => $faker->randomNumber(8),
        'nombre' => $faker->Firstname,
        'direccion' => $faker->streetAddress,
        'telefono' => $faker->e164PhoneNumber
    ];
});

$factory->define(Place::class, function(Faker\Generator $faker){
    return [
        'codigo' => $faker->countryCode,
        'nombre' => $faker->word
    ];
});

$factory->define(Travel::class, function(Faker\Generator $faker){
    $traveler = factory(App\Models\Traveler::class)->create();
    $origen = factory(App\Models\Place::class)->create();
    $destino = factory(App\Models\Place::class)->create();
    return [
        'codigo' => $faker->sha1,
        'plazas' => $faker->randomNumber(2),
        'fecha' =>  $faker->dateTime,
        'traveler_id' => $traveler->id,
        'origen_id' => $origen->id,
        'destino_id' => $destino->id
    ];
});


