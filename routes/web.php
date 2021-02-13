<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    // return $router->app->version();
    return $router->app->version();
});

// GET GENERATE APP KEY
$router->get('/key', function () {
    return \Illuminate\Support\Str::random(32);
});

// user
// -- GET METHOD --
$router->get('/user/profile/{id_user}', 'UserController@getProfile');
// -- POST METHOD
$router->post('/user/register', 'UserController@register');
$router->post('/user/login', 'UserController@login');
$router->post('/user/profile', 'UserController@setProfile');

// school
// -- GET METHOD --
$router->get('/school', 'SchoolController@getList');
// -- POST METHOD --
$router->post('/school', 'SchoolController@store');

// activity
// -- GET METHOD --
$router->get('/activity', 'ActivityController@getList');
// -- POST METHOD --
$router->post('/activity', 'ActivityController@store');

// report
// -- GET METHOD --
// -- POST METHOD --
$router->post('/report', 'ReportController@store');
