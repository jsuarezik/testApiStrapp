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
    $app->get('users/{id}/created_tasks', 'UserController@getCreatedTasks');
    $app->get('users/{id}/assigned_tasks', 'UserController@getAssignedTasks');
    $app->get('users/{id}', 'UserController@get');
    $app->post('users','UserController@add');
    $app->patch('users/{id}', 'UserController@patch');
    $app->delete('users/{id}', 'UserController@delete');
    //Priorities
    $app->get('priorities', 'PriorityController@all');
    $app->get('priorities/{id}', 'PriorityController@get');
    $app->post('priorities', 'PriorityController@add');
    $app->patch('priorities/{id}', 'PriorityController@patch');
    $app->delete('priorities/{id}', 'PriorityController@delete');
    //Tasks
    $app->get('tasks', 'TaskController@all');
    $app->get('tasks/{id}', 'TaskController@get');
    $app->get('tasks/{id}/creator_user', 'TaskController@getCreatorUser');
    $app->get('tasks/{id}/assigned_user', 'TaskController@getAssignedUser');
    $app->post('tasks', 'TaskController@add');
    $app->post('tasks/{id}/user/{user_id}', 'TaskController@assignTask');
    $app->patch('tasks/{id}', 'TaskController@patch');
    $app->delete('tasks/{id}', 'TaskController@delete');

});
