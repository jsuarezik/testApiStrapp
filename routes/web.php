<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/api', function () use ($app) {
    return ['v1'];
});

$app->group([
    'prefix' => 'api/v1',
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->post('auth/login', 'AuthController@postLogin');
});

$app->group([
    'prefix' => 'api/v1',
    'middleware' => ['before' => 'jwt-auth'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('profile/me', 'ProfileController@me');
    //Users
    $app->get('users','UserController@all');
    $app->get('users/{id}', 'UserController@get');
    $app->post('users','UserController@add');
    $app->patch('users/{id}', 'UserController@patch');
    $app->delete('users/{id}', 'UserController@delete');
    //Traveler
    $app->get('travelers', 'TravelerController@all');
    $app->get('travelers/{id}', 'TravelerController@get');
    $app->get('travelers/{id}/travels', 'TravelerController@getTravels');
    $app->post('travelers', 'TravelerController@add');
    $app->patch('travelers/{id}', 'TravelerController@patch');
    $app->delete('travelers/{id}', 'TravelerController@delete');
    //Place
    $app->get('places', 'PlaceController@all');
    $app->get('places/{id}', 'PlaceController@get');
    $app->get('places/{id}/origin_travels', 'PlaceController@getOriginTravels');
    $app->get('places/{id}/destination_travels' , 'PlaceController@getDestinationTravels');
    $app->post('places', 'PlaceController@add');
    $app->patch('places/{id}', 'PlaceController@patch');
    $app->delete('places/{id}', 'PlaceController@delete');
    //Travels
    $app->get('travels', 'TravelController@all');
    $app->get('travels/{id}', 'TravelController@get');
    $app->get('travels/{id}/origin', 'TravelController@getOriginPlace');
    $app->get('travels/{id}/destination' , 'TravelController@getDestinationPlace');
    $app->get('travels/{id}/traveler' , 'TravelController@getTraveler');
    $app->post('travels', 'TravelController@add');
    $app->patch('travels/{id}', 'TravelController@patch');
    $app->delete('travels/{id}', 'TravelController@delete');

});
